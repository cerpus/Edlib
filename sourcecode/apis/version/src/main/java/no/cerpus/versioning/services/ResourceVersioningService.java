package no.cerpus.versioning.services;


import no.cerpus.versioning.models.ResourceVersions;

import java.util.Collection;

public interface ResourceVersioningService {

    ResourceVersions findResource(String id);

    ResourceVersions storeResource(ResourceVersions resourceVersion) throws ResourceVersioningLinearVersioningException;

    ResourceVersions updateResourceWithCoreData(String externalSystem, String externalId, String CoreId, String CoreUrl);

    ResourceVersions findOriginResources(String originSystem, String originReference);

    ResourceVersions findResourceByExternalProperties(String externalSystem, String externalId);

    ResourceVersions findLatestVersion(String id);

    Collection<ResourceVersions> findVersionParents(String id);

    Collection<ResourceVersions> getLeafs(String id);

    class ResourceVersioningLinearVersioningException extends Exception {
        private ResourceVersions requestedParent;

        public ResourceVersioningLinearVersioningException(ResourceVersions requestedParent) {
            super("The parent must be a leaf node when linear versioning is used");
            this.requestedParent = requestedParent;
        }

        public ResourceVersions getRequestedParent() {
            return requestedParent;
        }
    }
}
