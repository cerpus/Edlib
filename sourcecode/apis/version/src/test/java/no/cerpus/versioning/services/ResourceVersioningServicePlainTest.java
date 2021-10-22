package no.cerpus.versioning.services;

import no.cerpus.versioning.models.ResourceVersions;
import no.cerpus.versioning.repository.ResourceVersionRepository;
import org.junit.Before;
import org.junit.Test;
import org.mockito.Mockito;
import org.springframework.dao.DataIntegrityViolationException;

import javax.validation.ConstraintViolationException;
import java.util.List;
import java.util.Optional;
import java.util.Set;

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.fail;
import static org.mockito.BDDMockito.given;
import static org.mockito.Mockito.mock;

public class ResourceVersioningServicePlainTest {
    private ResourceVersionRepository resourceVersionRepository;
    private ResourceVersioningServiceImpl resourceVersioningService;

    @Before
    public void beforeTesting() {
        resourceVersionRepository = mock(ResourceVersionRepository.class);
        resourceVersioningService = new ResourceVersioningServiceImpl();
        resourceVersioningService.setResourceVersionRepository(resourceVersionRepository);
    }

    private <T> T assertNoReduction(T a, T b) {
        fail("No reduction assertion");
        return a;
    }
    private <T> T assertNoOrElse() {
        fail("No or else assertion");
        return null;
    }

    @Test(expected = ResourceVersioningService.ResourceVersioningLinearVersioningException.class)
    public void testStoreLinearConflictVersion1() throws ResourceVersioningService.ResourceVersioningLinearVersioningException {
        var grandchild = new ResourceVersions("Test", "1", "http://cerpus.com");
        grandchild.setId("grandchild");
        var child = new ResourceVersions("Test", "2", "http://cerpus.com");
        child.setId("child");
        child.setChildren(List.of(grandchild));
        var parentVersion = new ResourceVersions("Test", "3", "http://cerpus.com");
        parentVersion.setId("parent");
        parentVersion.setChildren(List.of(child));
        given(resourceVersionRepository.findByIdWithChildren("parent")).willReturn(Optional.of(parentVersion));
        var storeVersion = new ResourceVersions("Test", "4", "http://cerpus.com");
        storeVersion.setParent("parent");
        storeVersion.setLinearVersioning(true);
        given(resourceVersionRepository.save(storeVersion)).willThrow(new DataIntegrityViolationException("Constraint violation"));
        try {
            resourceVersioningService.storeResource(storeVersion);
        } catch (ResourceVersioningService.ResourceVersioningLinearVersioningException e) {
            assertEquals(parentVersion, e.getRequestedParent());
            throw e;
        }
    }

    @Test(expected = ResourceVersioningService.ResourceVersioningLinearVersioningException.class)
    public void testStoreLinearConflictVersion2() throws ResourceVersioningService.ResourceVersioningLinearVersioningException {
        var grandchild = new ResourceVersions("Test", "1", "http://cerpus.com");
        grandchild.setId("grandchild");
        var child = new ResourceVersions("Test", "2", "http://cerpus.com");
        child.setId("child");
        child.setChildren(List.of(grandchild));
        child.setLinearVersioning(true);
        var parentVersion = new ResourceVersions("Test", "3", "http://cerpus.com");
        parentVersion.setId("parent");
        parentVersion.setChildren(List.of(child));
        given(resourceVersionRepository.findByIdWithChildren("parent")).willReturn(Optional.of(parentVersion));
        var storeVersion = new ResourceVersions("Test", "4", "http://cerpus.com");
        storeVersion.setParent("parent");
        storeVersion.setLinearVersioning(false);
        given(resourceVersionRepository.save(storeVersion)).willThrow(new DataIntegrityViolationException("Constraint violation"));
        try {
            resourceVersioningService.storeResource(storeVersion);
        } catch (ResourceVersioningService.ResourceVersioningLinearVersioningException e) {
            assertEquals(parentVersion, e.getRequestedParent());
            throw e;
        }
    }

    @Test(expected = DataIntegrityViolationException.class)
    public void testStoreWithUnknownDataIntegrityError() throws ResourceVersioningService.ResourceVersioningLinearVersioningException {
        var grandchild = new ResourceVersions("Test", "1", "http://cerpus.com");
        grandchild.setId("grandchild");
        var child = new ResourceVersions("Test", "2", "http://cerpus.com");
        child.setId("child");
        child.setChildren(List.of(grandchild));
        var parentVersion = new ResourceVersions("Test", "3", "http://cerpus.com");
        parentVersion.setId("parent");
        parentVersion.setChildren(List.of(child));
        given(resourceVersionRepository.findByIdWithChildren("parent")).willReturn(Optional.of(parentVersion));
        var storeVersion = new ResourceVersions("Test", "4", "http://cerpus.com");
        storeVersion.setParent("parent");
        storeVersion.setLinearVersioning(false);
        given(resourceVersionRepository.save(storeVersion)).willThrow(new DataIntegrityViolationException("Constraint violation"));
        resourceVersioningService.storeResource(storeVersion);
    }

    @Test
    public void testStoreLinearVersioning() throws ResourceVersioningService.ResourceVersioningLinearVersioningException {
        var parentVersion = new ResourceVersions("Test", "3", "http://cerpus.com");
        parentVersion.setId("parent");
        parentVersion.setChildren(List.of());
        given(resourceVersionRepository.findByIdWithChildren("parent")).willReturn(Optional.of(parentVersion));
        var storeVersion = new ResourceVersions("Test", "4", "http://cerpus.com");
        storeVersion.setParent("parent");
        storeVersion.setLinearVersioning(false);
        /*
         * Mocked save returns the same object. In real life save will often return
         * a new object.
         */
        given(resourceVersionRepository.save(storeVersion)).willReturn(storeVersion);
        assertEquals(storeVersion, resourceVersioningService.storeResource(storeVersion));
    }

    @Test
    public void testLinearVersioningConstraintFail1() {
        final var inputResourceVersions = new ResourceVersions("ContentAuthor", "3", null);
        inputResourceVersions.setVersionPurpose("Update");
        inputResourceVersions.setParent("parent");
        inputResourceVersions.setLinearVersioning(true);
        {
            final var parentResourceVersions = new ResourceVersions("ContentAuthor", "1", null);
            final var siblingResourceVersions = new ResourceVersions("ContentAuthor", "2", null);
            siblingResourceVersions.setVersionPurpose("Update");
            siblingResourceVersions.setParent("parent");
            siblingResourceVersions.setParentVersion(parentResourceVersions);
            siblingResourceVersions.setLinearVersioning(false);
            parentResourceVersions.setId("parent");
            parentResourceVersions.setVersionPurpose("Create");
            parentResourceVersions.setChildren(List.of(siblingResourceVersions));
            given(resourceVersionRepository.findByIdWithChildren("parent")).willReturn(Optional.of(parentResourceVersions));
        }
        given(resourceVersionRepository.save(Mockito.any(ResourceVersions.class))).willThrow(new ConstraintViolationException(Set.of()));
        try {
            resourceVersioningService.storeResource(inputResourceVersions);
            fail();
        } catch (ResourceVersioningService.ResourceVersioningLinearVersioningException e) {
            System.out.println("As expected: Linear versioning exception");
        }
    }

    @Test
    public void testLinearVersioningConstraintFail2() {
        final var inputResourceVersions = new ResourceVersions("ContentAuthor", "3", null);
        inputResourceVersions.setVersionPurpose("Update");
        inputResourceVersions.setParent("parent");
        inputResourceVersions.setLinearVersioning(false);
        {
            final var parentResourceVersions = new ResourceVersions("ContentAuthor", "1", null);
            final var siblingResourceVersions = new ResourceVersions("ContentAuthor", "2", null);
            siblingResourceVersions.setVersionPurpose("Update");
            siblingResourceVersions.setParent("parent");
            siblingResourceVersions.setParentVersion(parentResourceVersions);
            siblingResourceVersions.setLinearVersioning(true);
            parentResourceVersions.setId("parent");
            parentResourceVersions.setVersionPurpose("Create");
            parentResourceVersions.setChildren(List.of(siblingResourceVersions));
            given(resourceVersionRepository.findByIdWithChildren("parent")).willReturn(Optional.of(parentResourceVersions));
        }
        given(resourceVersionRepository.save(Mockito.any(ResourceVersions.class))).willThrow(new ConstraintViolationException(Set.of()));
        try {
            resourceVersioningService.storeResource(inputResourceVersions);
            fail();
        } catch (ResourceVersioningService.ResourceVersioningLinearVersioningException e) {
            System.out.println("As expected: Linear versioning exception");
        }
    }

    @Test
    public void testLinearVersioningConstraintSpuriousFail1() {
        final var inputResourceVersions = new ResourceVersions("ContentAuthor", "3", null);
        inputResourceVersions.setVersionPurpose("Update");
        inputResourceVersions.setParent("parent");
        inputResourceVersions.setLinearVersioning(false);
        {
            final var parentResourceVersions = new ResourceVersions("ContentAuthor", "1", null);
            final var siblingResourceVersions = new ResourceVersions("ContentAuthor", "2", null);
            siblingResourceVersions.setVersionPurpose("Update");
            siblingResourceVersions.setParent("parent");
            siblingResourceVersions.setParentVersion(parentResourceVersions);
            siblingResourceVersions.setLinearVersioning(false);
            parentResourceVersions.setId("parent");
            parentResourceVersions.setVersionPurpose("Create");
            parentResourceVersions.setChildren(List.of(siblingResourceVersions));
            given(resourceVersionRepository.findByIdWithChildren("parent")).willReturn(Optional.of(parentResourceVersions));
        }
        given(resourceVersionRepository.save(Mockito.any(ResourceVersions.class))).willThrow(new ConstraintViolationException(Set.of()));
        try {
            resourceVersioningService.storeResource(inputResourceVersions);
            fail();
        } catch (ResourceVersioningService.ResourceVersioningLinearVersioningException e) {
            fail();
        } catch (ConstraintViolationException e) {
            System.out.println("Expected passed through exception");
        }
    }

    @Test
    public void testLinearVersioningConstraintSpuriousFail2() {
        final var inputResourceVersions = new ResourceVersions("ContentAuthor", "3", null);
        inputResourceVersions.setVersionPurpose("Translation");
        inputResourceVersions.setParent("parent");
        inputResourceVersions.setLinearVersioning(true);
        {
            final var parentResourceVersions = new ResourceVersions("ContentAuthor", "1", null);
            final var siblingResourceVersions = new ResourceVersions("ContentAuthor", "2", null);
            siblingResourceVersions.setVersionPurpose("Update");
            siblingResourceVersions.setParent("parent");
            siblingResourceVersions.setParentVersion(parentResourceVersions);
            siblingResourceVersions.setLinearVersioning(true);
            parentResourceVersions.setId("parent");
            parentResourceVersions.setVersionPurpose("Create");
            parentResourceVersions.setChildren(List.of(siblingResourceVersions));
            given(resourceVersionRepository.findByIdWithChildren("parent")).willReturn(Optional.of(parentResourceVersions));
        }
        given(resourceVersionRepository.save(Mockito.any(ResourceVersions.class))).willThrow(new ConstraintViolationException(Set.of()));
        try {
            resourceVersioningService.storeResource(inputResourceVersions);
            fail();
        } catch (ResourceVersioningService.ResourceVersioningLinearVersioningException e) {
            fail();
        } catch (ConstraintViolationException e) {
            System.out.println("Expected passed through exception");
        }
    }
}
