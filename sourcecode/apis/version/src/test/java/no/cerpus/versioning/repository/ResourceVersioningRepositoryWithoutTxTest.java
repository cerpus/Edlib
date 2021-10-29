package no.cerpus.versioning.repository;


import no.cerpus.versioning.models.ResourceVersions;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.context.SpringBootTest;
import org.springframework.dao.DataIntegrityViolationException;
import org.springframework.test.context.junit4.SpringRunner;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

import static org.junit.Assert.*;

@RunWith(SpringRunner.class)
@SpringBootTest
public class ResourceVersioningRepositoryWithoutTxTest {
    @Autowired
    protected ResourceVersionRepository resourceVersionRepository;

    @Test
    public void testWithoutLinearConstraint() {
        var root = new ResourceVersions("Test", "root", "http://cerpus.com");
        root = resourceVersionRepository.save(root);
        var delete = new ArrayList<>(List.of(root));
        try {
            var v1 = new ResourceVersions("Test", "v1", "http://cerpus.com");
            v1.setParent(root.getId());
            v1 = resourceVersionRepository.save(v1);
            delete.add(v1);
            var v2 = new ResourceVersions("Test", "v2", "http://cerpus.com");
            v2.setParent(root.getId());
            v2 = resourceVersionRepository.save(v2);
            delete.add(v2);
        } finally {
            Collections.reverse(delete);
            resourceVersionRepository.deleteAll(delete);
        }
    }

    @Test
    public void testLinearConstraint1() {
        var root = new ResourceVersions("Test", "root", "http://cerpus.com");
        root = resourceVersionRepository.save(root);
        var delete = new ArrayList<>(List.of(root));
        try {
            var v1 = new ResourceVersions("Test", "v1", "http://cerpus.com");
            v1.setParent(root.getId());
            v1 = resourceVersionRepository.save(v1);
            delete.add(v1);
            var v2 = new ResourceVersions("Test", "v2", "http://cerpus.com");
            v2.setParent(root.getId());
            v2.setLinearVersioning(true);
            boolean constraintHit = false;
            try {
                v2 = resourceVersionRepository.save(v2);
                delete.add(v2);
            } catch (DataIntegrityViolationException e) {
                assertTrue(e.getMessage().toUpperCase().contains("CHECK_LINEAR_VERSIONING"));
                constraintHit = true;
            }
            assertTrue(constraintHit);
        } finally {
            Collections.reverse(delete);
            resourceVersionRepository.deleteAll(delete);
        }
    }

    @Test
    public void testLinearConstraint2() {
        var root = new ResourceVersions("Test", "root", "http://cerpus.com");
        root = resourceVersionRepository.save(root);
        var delete = new ArrayList<>(List.of(root));
        try {
            var v1 = new ResourceVersions("Test", "v1", "http://cerpus.com");
            v1.setParent(root.getId());
            v1.setLinearVersioning(true);
            v1 = resourceVersionRepository.save(v1);
            delete.add(v1);
            var v2 = new ResourceVersions("Test", "v2", "http://cerpus.com");
            v2.setParent(root.getId());
            boolean constraintHit = false;
            try {
                v2 = resourceVersionRepository.save(v2);
                delete.add(v2);
            } catch (DataIntegrityViolationException e) {
                assertTrue(e.getMessage().toUpperCase().contains("CHECK_LINEAR_VERSIONING"));
                constraintHit = true;
            }
            assertTrue(constraintHit);
        } finally {
            Collections.reverse(delete);
            resourceVersionRepository.deleteAll(delete);
        }
    }

    @Test
    public void testLinearConstraint3() {
        var root = new ResourceVersions("Test", "root", "http://cerpus.com");
        root = resourceVersionRepository.save(root);
        var delete = new ArrayList<>(List.of(root));
        try {
            var v1 = new ResourceVersions("Test", "v1", "http://cerpus.com");
            v1.setParent(root.getId());
            v1.setLinearVersioning(true);
            v1 = resourceVersionRepository.save(v1);
            delete.add(v1);
            var v2 = new ResourceVersions("Test", "v2", "http://cerpus.com");
            v2.setParent(root.getId());
            v2.setLinearVersioning(true);
            boolean constraintHit = false;
            try {
                v2 = resourceVersionRepository.save(v2);
                delete.add(v2);
            } catch (DataIntegrityViolationException e) {
                assertTrue(e.getMessage().toUpperCase().contains("CHECK_LINEAR_VERSIONING"));
                constraintHit = true;
            }
            assertTrue(constraintHit);
        } finally {
            Collections.reverse(delete);
            resourceVersionRepository.deleteAll(delete);
        }
    }
}
