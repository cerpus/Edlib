package no.cerpus.versioning.services;


import no.cerpus.versioning.exceptions.ResourceNotFoundException;
import no.cerpus.versioning.ResourceVersionData;
import no.cerpus.versioning.models.ResourceVersions;
import org.junit.Rule;
import org.junit.Test;
import org.junit.rules.ExpectedException;
import org.junit.runner.RunWith;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.context.SpringBootTest;
import org.springframework.test.context.junit4.SpringRunner;

import java.time.Instant;
import java.time.temporal.ChronoUnit;
import java.util.Date;
import java.util.List;
import java.util.NoSuchElementException;

import static org.junit.Assert.*;


@RunWith(SpringRunner.class)
@SpringBootTest(webEnvironment = SpringBootTest.WebEnvironment.MOCK)
public class ResourceVersioningServiceTest extends ResourceVersionData {

    @Rule
    public final ExpectedException exception = ExpectedException.none();

    @Autowired
    private ResourceVersioningService resourceVersioningService;

    @Test
    public void createResource() throws Exception {
        ResourceVersions resource = resourceVersioningService.storeResource(new ResourceVersions("ServiceTest", "ServiceId-1", "http://test.test"));
        List<ResourceVersions> resources = (List<ResourceVersions>) resourceVersionRepository.findAll();
        int numberOfResources = this.resources.size() + 1;
        assertEquals(numberOfResources, resources.size());

        ResourceVersions newResource = resourceVersioningService.storeResource(new ResourceVersions("ServiceTest", "ServiceId-1", "http://test.test"));
        assertEquals(numberOfResources, resources.size());
        assertEquals(resource, newResource);

        ResourceVersions initialResourceWithoutDate = resourceVersioningService.storeResource(new ResourceVersions("ServiceTest", "ServiceId-2", "http://test.testing", "initial"));
        assertNotNull(initialResourceWithoutDate.getCreatedAt());

        Date createdAt = Date.from(Instant.now().minus(1, ChronoUnit.DAYS));
        ResourceVersions initialResourceWithDate = new ResourceVersions("ServiceTest", "ServiceId-3", "http://test.testing", "initial");
        initialResourceWithDate.setCreatedAt(createdAt);
        ResourceVersions storedWithCreatedAt = resourceVersioningService.storeResource(initialResourceWithDate);
        assertEquals(storedWithCreatedAt.getCreatedAt(), createdAt);
    }

    @Test
    public void createdAtInTheFuture_thenFail() throws ResourceVersioningService.ResourceVersioningLinearVersioningException {
        exception.expect(Exception.class);
        exception.expectMessage("The created time is in the future");

        Date createdAt = Date.from(Instant.now().plus(1, ChronoUnit.DAYS));
        ResourceVersions initialResourceWithDate = new ResourceVersions("ServiceTest", "FutureId", "http://test.testing", "initial");
        initialResourceWithDate.setCreatedAt(createdAt);
        resourceVersioningService.storeResource(initialResourceWithDate);
    }

    @Test
    public void findResource() throws ResourceVersioningService.ResourceVersioningLinearVersioningException {
        ResourceVersions resourceVersion = resourceVersioningService.storeResource(new ResourceVersions("ServiceTest", "ServiceId-1", "http://test.test"));
        ResourceVersions dbResource = resourceVersioningService.findResource(resourceVersion.getId());
        assertNotNull(dbResource);
        assertEquals(resourceVersion, dbResource);
    }

    @Test
    public void updateResourceWithCoreData() throws Exception {
        ResourceVersions resource = getLocalResource(5);
        ResourceVersions modifiedResource = resourceVersioningService.updateResourceWithCoreData(resource.getExternalSystem(), resource.getExternalReference(), "aaabbbccc", "http://core.url");

        assertNull(resource.getCoreId());
        assertNotNull(modifiedResource.getCoreId());
        assertEquals("aaabbbccc", modifiedResource.getCoreId());
        assertEquals("http://core.url", modifiedResource.getExternalUrl());

        resource = getLocalResource(6);
        ResourceVersions modifiedResourceWithNoUrl = resourceVersioningService.updateResourceWithCoreData(resource.getExternalSystem(), resource.getExternalReference(), "aaabbbccc", null);

        assertNull(resource.getCoreId());
        assertNotNull(modifiedResourceWithNoUrl.getCoreId());
        assertEquals("aaabbbccc", modifiedResourceWithNoUrl.getCoreId());
        assertEquals(resource.getExternalUrl(), modifiedResourceWithNoUrl.getExternalUrl());

        resource = getLocalResource(7);
        ResourceVersions modifiedResourceWithEmptyUrl = resourceVersioningService.updateResourceWithCoreData(resource.getExternalSystem(), resource.getExternalReference(), "aaabbbccc", "");

        assertNull(resource.getCoreId());
        assertNotNull(modifiedResourceWithEmptyUrl.getCoreId());
        assertEquals("aaabbbccc", modifiedResourceWithEmptyUrl.getCoreId());
        assertEquals(resource.getExternalUrl(), modifiedResourceWithEmptyUrl.getExternalUrl());

        exception.expect(ResourceNotFoundException.class);
        exception.expectMessage("The resource 'NotValid - NotValid' was not found.");
        ResourceVersions invalidResource = resourceVersioningService.updateResourceWithCoreData("NotValid", "NotValid", "11111", "http://core.url");
    }

    @Test
    public void findOriginResourcesEmptyParameters() throws Exception {
        exception.expect(RuntimeException.class);
        exception.expectMessage("The fields 'originSystem' or 'originReference' cannot be empty");
        resourceVersioningService.findOriginResources("", "");
    }

    @Test
    public void findOriginResourcesValidParameters() throws Exception {
        ResourceVersions dbResource = resourceVersioningService.findOriginResources("TestImport", "333");
        assertNotNull(dbResource);
        assertEquals(getLocalResource(2), dbResource);
    }

    @Test
    public void findOriginResourcesValidInParameters() {
        exception.expect(ResourceNotFoundException.class);
        exception.expectMessage("The resource 'NotValid - Bogus' was not found.");
        resourceVersioningService.findOriginResources("NotValid", "Bogus");
    }

    @Test
    public void storeWithParent() throws ResourceVersioningService.ResourceVersioningLinearVersioningException {
        ResourceVersions resource = resourceVersioningService.storeResource(new ResourceVersions("ServiceTest", "ServiceId-1", "http://test.me.hard", "create", getLocalResource(0).getId()));
        assertEquals(resource, resourceVersioningService.findResource(resource.getId()));

        exception.expect(NoSuchElementException.class);
        exception.expectMessage("Could not find the specified parent. Aborting.");

        resource = new ResourceVersions("ServiceTest", "ServiceId-2", "http://test.me.hard", "modifiy");
        resource.setParent("InvalidParentPointer");
        resourceVersioningService.storeResource(resource);
    }

    @Test
    public void findLatestVersion_NoChildren() {
        ResourceVersions resource = resourceVersioningService.findLatestVersion(resources.get(0).getId());
        assertEquals(resources.get(0), resource);
    }

    @Test
    public void findLatestVersionInSubtree() {
        ResourceVersions resource = resourceVersioningService.findLatestVersion(resources.get(8).getId());
        assertEquals(resources.get(9), resource);
    }

    @Test
    public void findLatestVersionWithInvalidId() {
        exception.expect(NoSuchElementException.class);
        exception.expectMessage("Could not find any records with the id 'InvalidId'");

        resourceVersioningService.findLatestVersion("InvalidId");
    }

    @Test
    public void findResourceByEmptyExternalProperties() {
        exception.expect(RuntimeException.class);
        exception.expectMessage("The fields 'externalSystem' or 'externalId' cannot be empty");

        resourceVersioningService.findResourceByExternalProperties("", "");
    }

    @Test
    public void findResourceByExternalProperties() {
        ResourceVersions resource = resourceVersioningService.findResourceByExternalProperties(resources.get(0).getExternalSystem(), resources.get(0).getExternalReference());
        assertEquals(resources.get(0), resource);

        exception.expect(ResourceNotFoundException.class);
        exception.expectMessage("The resource 'FakeSystem - BogusId' was not found.");

        resourceVersioningService.findResourceByExternalProperties("FakeSystem", "BogusId");
    }
}
