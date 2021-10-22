package no.cerpus.versioning.web;

import com.fasterxml.jackson.annotation.JsonView;
import no.cerpus.versioning.Constants;
import no.cerpus.versioning.exceptions.ResourceNotFoundException;
import no.cerpus.versioning.models.ResourceVersions;
import no.cerpus.versioning.response.ResponseMessage;
import no.cerpus.versioning.services.ResourceVersioningService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.validation.BindingResult;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.*;

import java.net.URI;
import java.util.Collection;

@RestController
@RequestMapping(Constants.API_VERSION + Constants.API_PREFIX)
public class ResourceVersioningController {

    @Autowired
    private ResourceVersioningService resourceVersioningServiceImpl;

    @GetMapping(value = "/{id}")
    @ResponseBody
    public ResponseEntity<ResponseMessage<ResourceVersions>> findResourceVersion(@PathVariable String id) {
        ResourceVersions resource = resourceVersioningServiceImpl.findResource(id);
        if (resource == null) {
            throw new ResourceNotFoundException(id);
        }
        return ResponseEntity.ok(ResponseMessage.success(resource));
    }

    @GetMapping(value = "/{id}/parents")
    @ResponseBody
    public ResponseEntity<ResponseMessage<Collection<ResourceVersions>>> findResourceParents(@PathVariable String id) {
        return ResponseEntity.ok(ResponseMessage.success(resourceVersioningServiceImpl.findVersionParents(id)));
    }

    @PostMapping(
            params = {"externalSystem", "externalReference", "externalUrl"}
    )
    @ResponseBody
    public ResponseEntity<ResponseMessage<ResourceVersions>> createResourceVersion(@Validated ResourceVersions resourceVersions, BindingResult errors) throws ResourceVersioningService.ResourceVersioningLinearVersioningException {
        ResourceVersions storeResource = resourceVersioningServiceImpl.storeResource(resourceVersions);
        if (storeResource == null) {
            throw new RuntimeException("Could not create element");
        }

        return ResponseEntity.created(URI.create(Constants.API_VERSION + Constants.API_PREFIX + "/" + storeResource.getId())).body(ResponseMessage.success(storeResource));
    }

    @PostMapping(
            consumes = {MediaType.APPLICATION_JSON_VALUE}
    )
    @ResponseBody
    public ResponseEntity<ResponseMessage<ResourceVersions>> createResourceVersionFromJsons(@Validated @RequestBody ResourceVersions resourceVersions, BindingResult errors) throws ResourceVersioningService.ResourceVersioningLinearVersioningException {
        ResourceVersions storeResource = resourceVersioningServiceImpl.storeResource(resourceVersions);
        if (storeResource == null) {
            throw new RuntimeException("Could not create element");
        }

        return ResponseEntity.created(URI.create(Constants.API_VERSION + Constants.API_PREFIX + "/" + storeResource.getId())).body(ResponseMessage.success(storeResource));
    }


    @PutMapping(value = Constants.API_CORE)
    @ResponseBody
    public ResponseEntity<ResponseMessage<ResourceVersions>> updateResourceWithCoreId(@Validated(ResourceVersions.UpdateFromCoreValidation.class) ResourceVersions resource, BindingResult errors) {
        resource = resourceVersioningServiceImpl.updateResourceWithCoreData(resource.getExternalSystem(), resource.getExternalReference(), resource.getCoreId(), resource.getExternalUrl());

        return ResponseEntity.ok(ResponseMessage.success(resource));
    }

    @JsonView(ResourceVersions.OriginView.class)
    @GetMapping(value = "/origin/{originSystem}/{originReference}")
    public ResponseEntity<ResponseMessage> filterVersions(@PathVariable String originSystem, @PathVariable String originReference) {
        ResourceVersions resource = resourceVersioningServiceImpl.findOriginResources(originSystem, originReference);
        return ResponseEntity.ok(ResponseMessage.success(resource));
    }


    @GetMapping(value = Constants.API_LATEST)
    public ResponseEntity<ResponseMessage<ResourceVersions>> getLatestVersion(@PathVariable String id) {
        ResourceVersions resource = resourceVersioningServiceImpl.findLatestVersion(id);
        return ResponseEntity.ok(ResponseMessage.success(resource));
    }

    @GetMapping(value = Constants.API_EXTERNAL)
    public ResponseEntity<ResponseMessage<ResourceVersions>> getVersionFromExternalProperties(@PathVariable String externalSystem, @PathVariable String externalId) {
        ResourceVersions resource = resourceVersioningServiceImpl.findResourceByExternalProperties(externalSystem, externalId);
        return ResponseEntity.ok(ResponseMessage.success(resource));
    }

}
