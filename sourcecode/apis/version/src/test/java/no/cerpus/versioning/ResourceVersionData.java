package no.cerpus.versioning;


import no.cerpus.versioning.models.ResourceVersions;
import no.cerpus.versioning.repository.ResourceVersionRepository;
import org.junit.After;
import org.junit.Before;
import org.springframework.beans.factory.annotation.Autowired;

import java.util.*;

abstract public class ResourceVersionData {

    @Autowired
    protected ResourceVersionRepository resourceVersionRepository;

    protected List<ResourceVersions> resources = new ArrayList<>();

    @Before
    public void setUp() throws Exception {
        resources.add(new ResourceVersions("Microsoft", "external_1", "http://micro.test", "create"));
        resources.add(new ResourceVersions("Google", "external_2", "http://google.test", "create"));
        resources.add(new ResourceVersions("Cerpus", "external_3", "http://cerpus.test", "import", "TestImport", "333"));
        resources.add(new ResourceVersions("Cerpus", "external_4", "http://cerpus.test", "import", "TestImport", "444"));
        resources.add(new ResourceVersions("Yahoo", "external_3_1", "http://yahoo.test", "spelling"));
        resources.add(new ResourceVersions("Amazon", "external_1", "http://amazon.test"));
        resources.add(new ResourceVersions("Yeeeha", "external_4_1", "http://yepp.test/hey", "modified"));
        resources.add(new ResourceVersions("Google", "external_2_1", "http://goog.test/hey", "modified"));
        resources.add(new ResourceVersions("Google", "external_2_2", "http://goog.test/hey", "modified"));
        resources.add(new ResourceVersions("Google", "external_2_2_1", "http://goog.test/hey", "modified"));
        resources.add(new ResourceVersions("Google", "external_2_1_1", "http://goog.test/hey", "modified"));

        for (ResourceVersions resource : resources) {
            resourceVersionRepository.save(resource);
        }

        resources.get(4).setParent(resources.get(2).getId());
        resources.get(6).setParent(resources.get(3).getId());
        resources.get(7).setParent(resources.get(1).getId());
        resources.get(8).setParent(resources.get(1).getId());
        resources.get(9).setParent(resources.get(8).getId());
        resources.get(10).setParent(resources.get(7).getId());

        for (ResourceVersions resource : resources) {
            resourceVersionRepository.save(resource);
        }

    }

    @After
    public void tearDown() throws Exception {
        Iterable<ResourceVersions> resourcesInDatabase = resourceVersionRepository.findAll();
        resourcesInDatabase
                .forEach(resource -> {
                    resource.setParent(null);
                });
        resourceVersionRepository.saveAll(resourcesInDatabase);
        resourceVersionRepository.deleteAll();
        resources.clear();
    }

    protected ResourceVersions getRandomLocalResource() {
        return getLocalResource(new Random().nextInt(resources.size() - 1));
    }

    protected ResourceVersions getLocalResource(int index) {
        return resources.get(index);
    }
}
