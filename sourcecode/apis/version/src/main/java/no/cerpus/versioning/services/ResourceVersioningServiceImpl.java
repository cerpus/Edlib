package no.cerpus.versioning.services;

import no.cerpus.versioning.exceptions.ResourceNotFoundException;
import no.cerpus.versioning.models.ResourceVersions;
import no.cerpus.versioning.repository.ResourceVersionRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.dao.DataIntegrityViolationException;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import javax.validation.ConstraintViolationException;
import java.util.*;
import java.util.function.BiFunction;
import java.util.stream.Collectors;
import java.util.stream.Stream;

@Service
public class ResourceVersioningServiceImpl implements ResourceVersioningService {
    private ResourceVersionRepository resourceVersionRepository;

    @Autowired(required = false)
    public void setResourceVersionRepository(ResourceVersionRepository resourceVersionRepository) {
        this.resourceVersionRepository = resourceVersionRepository;
    }

    @Override
    public ResourceVersions findResource(String id) {
        return resourceVersionRepository.findById(id).orElse(null);
    }

    private Collection<ResourceVersions> getLeafs(ResourceVersions resourceVersions) {
        if (resourceVersions.getChildren() == null || resourceVersions.getChildren().isEmpty()) {
            return List.of(resourceVersions);
        }
        return resourceVersions.getChildren().stream().map(this::getLeafs).flatMap(Collection::stream).collect(Collectors.toList());
    }
    @Override
    @Transactional(rollbackFor = ResourceVersioningLinearVersioningException.class)
    public ResourceVersions storeResource(ResourceVersions resourceVersions) throws ResourceVersioningLinearVersioningException {
        ResourceVersions existingResource = resourceVersionRepository.findByExternalSystemAndExternalReference(resourceVersions.getExternalSystem(), resourceVersions.getExternalReference());
        if (existingResource != null) {
            return existingResource;
        }

        if (resourceVersions.getParent() != null) {
            ResourceVersions parentResource = resourceVersionRepository.findByIdWithChildren(resourceVersions.getParent()).orElse(null);
            if (parentResource == null) {
                throw new NoSuchElementException("Could not find the specified parent. Aborting.");
            }
            resourceVersions.setParentVersion(parentResource);
        }

        try {
            var savedResourceVersions = resourceVersionRepository.save(resourceVersions);
            resourceVersionRepository.flush();
            return savedResourceVersions;
        } catch (ConstraintViolationException | DataIntegrityViolationException e) {
            var parent = resourceVersions.getParentVersion();
            var siblings = parent != null ? parent.getChildren() : null;
            final var siblingsWithSameVersionPurpose = siblings != null ? siblings.stream().filter(sib -> resourceVersions.getVersionPurpose() == null || resourceVersions.getVersionPurpose().equals(sib.getVersionPurpose())).collect(Collectors.toList()) : List.<ResourceVersions>of();
            final var siblingWithLinear = siblingsWithSameVersionPurpose.stream().anyMatch(ResourceVersions::isLinearVersioning);
            if (siblingWithLinear || (resourceVersions.isLinearVersioning() && !siblingsWithSameVersionPurpose.isEmpty())) {
                throw new ResourceVersioningLinearVersioningException(parent);
            } else {
                throw e;
            }
        }
    }

    @Override
    public ResourceVersions updateResourceWithCoreData(String externalSystem, String externalId, String CoreId, String CoreUrl) {
        ResourceVersions resource = resourceVersionRepository.findByExternalSystemAndExternalReference(externalSystem, externalId);
        if (resource == null) {
            throw new ResourceNotFoundException(String.format("%s - %s", externalSystem, externalId));
        }
        if (CoreId != null && CoreId.length() > 0) {
            resource.setCoreId(CoreId);
        }

        if (CoreUrl != null && CoreUrl.length() > 0) {
            resource.setExternalUrl(CoreUrl);
        }
        resourceVersionRepository.save(resource);
        return resource;
    }

    @Override
    public ResourceVersions findOriginResources(String originSystem, String originReference) {
        if (originSystem.isEmpty() || originReference.isEmpty()) {
            throw new RuntimeException("The fields 'originSystem' or 'originReference' cannot be empty");
        }
        ResourceVersions resource = resourceVersionRepository.findByOriginSystemAndOriginReference(originSystem, originReference);
        if (resource == null) {
            throw new ResourceNotFoundException(String.format("%s - %s", originSystem, originReference));
        }
        return resource;
    }

    public ResourceVersions findResourceByExternalProperties(String externalSystem, String externalId) {
        if (externalSystem.isEmpty() || externalId.isEmpty()) {
            throw new RuntimeException("The fields 'externalSystem' or 'externalId' cannot be empty");
        }
        ResourceVersions resource = resourceVersionRepository.findByExternalSystemAndExternalReference(externalSystem, externalId);
        if (resource == null) {
            throw new ResourceNotFoundException(String.format("%s - %s", externalSystem, externalId));
        }
        return resource;
    }

    @Override
    @Transactional
    public ResourceVersions findLatestVersion(String id) {
        Optional<ResourceVersions> result = resourceVersionRepository.findById(id).map(root -> {
            BiFunction<BiFunction, ResourceVersions, Stream<ResourceVersions>> findLeafs = (rec, resource) -> {
                BiFunction<BiFunction, ResourceVersions, Stream<ResourceVersions>> recurse = rec;
                if (resource.getChildren().isEmpty()) {
                    return Stream.of(resource);
                } else {
                    return resource.getChildren().stream()
                            .flatMap(child -> recurse.apply(recurse, child));
                }
            };

            Stream<ResourceVersions> leafs = findLeafs.apply(findLeafs, root);

            return leafs.reduce((leaf1, leaf2) -> {
                if (leaf1.getCreatedAt().toInstant().isAfter(leaf2.getCreatedAt().toInstant())) {
                    return leaf1;
                } else {
                    return leaf2;
                }
            }).orElse(null);
        });

        return result.orElseThrow(() -> new NoSuchElementException(String.format("Could not find any records with the id '%s'", id)));
    }

    private Collection<ResourceVersions> getParents(Collection<ResourceVersions> result, ResourceVersions resource) {
        if (resource == null) {
            return result;
        }

        result.add(resource);

        if (!resource.getVersionPurpose().equalsIgnoreCase("update")) {
            return result;
        }

        var parentVersion = resource.getParentVersion();
        if (resource.getParentVersion() == null) {
            return result;
        }

        return getParents(result, parentVersion);
    }

    @Override
    @Transactional
    public Collection<ResourceVersions> findVersionParents(String id) {
        return resourceVersionRepository
                .findById(id)
                .map(root -> this.getParents(new ArrayList<>(), root.getParentVersion()))
                .orElseThrow(() -> new NoSuchElementException(String.format("Could not find any records with the id '%s'", id)));
    }

    @Override
    @Transactional
    public Collection<ResourceVersions> getLeafs(String id) {
        return resourceVersionRepository.findById(id)
                .map(this::getLeafs)
                .orElse(List.of());
    }
}
