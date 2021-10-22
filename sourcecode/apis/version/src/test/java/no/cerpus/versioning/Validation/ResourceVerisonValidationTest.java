package no.cerpus.versioning.Validation;

import no.cerpus.versioning.models.ResourceVersions;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.context.SpringBootTest;
import org.springframework.test.context.junit4.SpringRunner;

import javax.validation.ConstraintViolation;
import javax.validation.Validator;
import java.util.Set;

import static org.junit.Assert.*;

@RunWith(SpringRunner.class)
@SpringBootTest(webEnvironment = SpringBootTest.WebEnvironment.MOCK)
public class ResourceVerisonValidationTest {

    @Autowired
    private Validator validator;

    @Test
    public void ResourceVersionDefaultValidation() {
        ResourceVersions resource = new ResourceVersions("Test", "test", "notvalidUrl");
        Set<ConstraintViolation<ResourceVersions>> violation = validator.validate(resource);
        assertEquals(1, violation.size());

        resource.setExternalUrl("http://test.test");
        violation = validator.validate(resource);
        assertEquals(0, violation.size());

/*
        resource.setCoreId("LongLongLongLongLongLongLongLongLongLongLongLongLongLongLongLongLongCoreId");
        violation = validator.validate(resource);
        assertEquals(1, violation.size());
        assertEquals(
                "Cannot be over 40 characters long",
                violation.iterator().next().getMessage()
        );

        resource.setCoreId("coreId");
        violation = validator.validate(resource);
        assertEquals(0, violation.size());
*/

        resource.setOriginSystem("ReallyReallyReallyReallyReallyReallyReallyReallyReallyReallyReallyReallyReallyReallyLongName");
        violation = validator.validate(resource);
        assertEquals(1, violation.size());

        resource.setOriginSystem("OriginSys");
        violation = validator.validate(resource);
        assertEquals(0, violation.size());

        resource.setOriginReference("TooooooooooooooooooooooooooooooooooooooooooooooooooooLongOrigingReference");
        violation = validator.validate(resource);
        assertEquals(1, violation.size());

        resource.setOriginReference("origReg");
        violation = validator.validate(resource);
        assertEquals(0, violation.size());

    }

    @Test
    public void ResourceVersionUpdateCoreValidation() {
        ResourceVersions resource = new ResourceVersions("Test", "test", "notValidUrl");
        Set<ConstraintViolation<ResourceVersions>> violation = validator.validate(resource, ResourceVersions.UpdateFromCoreValidation.class);
        assertEquals(0, violation.size());

        resource.setExternalUrl("http://test.test");
        violation = validator.validate(resource, ResourceVersions.UpdateFromCoreValidation.class);
        assertEquals(0, violation.size());

/*
        resource.setCoreId("LongLongLongLongLongLongLongLongLongLongLongLongLongLongLongLongLongCoreId");
        violation = validator.validate(resource, ResourceVersions.UpdateFromCoreValidation.class);
        assertEquals(1, violation.size());
        assertEquals(
                "Cannot be over 40 characters long",
                violation.iterator().next().getMessage()
        );

        resource.setCoreId("coreId");
        violation = validator.validate(resource, ResourceVersions.UpdateFromCoreValidation.class);
        assertEquals(0, violation.size());
*/

        resource.setOriginSystem("ReallyReallyReallyReallyReallyReallyReallyReallyReallyReallyReallyReallyReallyReallyLongName");
        violation = validator.validate(resource, ResourceVersions.UpdateFromCoreValidation.class);
        assertEquals(0, violation.size());

        resource.setOriginSystem("OriginSys");
        violation = validator.validate(resource, ResourceVersions.UpdateFromCoreValidation.class);
        assertEquals(0, violation.size());

    }
}
