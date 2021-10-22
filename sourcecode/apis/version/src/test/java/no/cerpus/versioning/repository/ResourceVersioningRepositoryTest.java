package no.cerpus.versioning.repository;


import no.cerpus.versioning.ResourceVersionData;
import no.cerpus.versioning.models.ResourceVersions;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.springframework.boot.test.context.SpringBootTest;
import org.springframework.test.context.junit4.SpringRunner;
import org.springframework.transaction.annotation.Transactional;

import java.util.*;

import static org.junit.Assert.*;

@RunWith(SpringRunner.class)
@SpringBootTest
@Transactional
public class ResourceVersioningRepositoryTest extends ResourceVersionData {

    @Test
    public void findAllResources() {
        List<ResourceVersions> resources = (List<ResourceVersions>) resourceVersionRepository.findAll();
        assertEquals(this.resources.size(), resources.size());
    }

    @Test
    public void findResourceById() {
        ResourceVersions resource = getRandomLocalResource();
        ResourceVersions dbResource = resourceVersionRepository.findById(resource.getId()).orElse(null);
        assertEquals(resource.getId(), dbResource.getId());
    }

    @Test
    public void findResourceByExternalNameAndReference() {
        ResourceVersions dbResource = resourceVersionRepository.findByExternalSystemAndExternalReference("Yahoo", "external_3_1");
        ResourceVersions resource = resources.get(4);
        assertEquals(resource.getId(), dbResource.getId());

        dbResource = resourceVersionRepository.findByExternalSystemAndExternalReference("Amazon", "external_1");
        resource = resources.get(5);
        assertNotNull(dbResource);
        assertEquals(resource.getId(), dbResource.getId());

        dbResource = resourceVersionRepository.findByExternalSystemAndExternalReference("NotValid", "bogus");
        assertNull(dbResource);
    }

    @Test
    public void findResourceByParentId() {
        List<ResourceVersions> dbResource = resourceVersionRepository.findByParent(resources.get(4).getParent());
        ResourceVersions resource = resources.get(4);
        assertNotNull(dbResource);
        assertEquals(resource.getId(), dbResource.get(0).getId());
    }

    @Test
    public void findResourceByOrigin() {
        ResourceVersions dbResource = resourceVersionRepository.findByOriginSystemAndOriginReference("Bogus", "Bogus reference");
        assertNull(dbResource);

        ResourceVersions resource = resources.get(3);
        dbResource = resourceVersionRepository.findByOriginSystemAndOriginReference("TestImport", "444");
        assertEquals(resource.getId(), dbResource.getId());
    }
}
