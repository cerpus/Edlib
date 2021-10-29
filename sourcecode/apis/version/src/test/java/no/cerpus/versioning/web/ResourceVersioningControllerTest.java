package no.cerpus.versioning.web;


import no.cerpus.versioning.aspects.ValidationAspect;
import no.cerpus.versioning.exceptions.ResourceNotFoundException;
import no.cerpus.versioning.models.ResourceVersions;
import no.cerpus.versioning.services.ResourceVersioningService;
import org.json.JSONObject;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.mockito.Mockito;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.autoconfigure.web.servlet.WebMvcTest;
import org.springframework.boot.test.context.TestConfiguration;
import org.springframework.boot.test.mock.mockito.MockBean;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.EnableAspectJAutoProxy;
import org.springframework.http.MediaType;
import org.springframework.test.context.junit4.SpringRunner;
import org.springframework.test.web.servlet.MockMvc;
import org.springframework.test.web.servlet.result.MockMvcResultMatchers;

import java.time.Instant;
import java.time.temporal.ChronoField;
import java.time.temporal.ChronoUnit;
import java.util.*;

import static org.hamcrest.Matchers.*;
import static org.mockito.BDDMockito.given;
import static org.mockito.Mockito.*;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.*;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.*;

@RunWith(SpringRunner.class)
@WebMvcTest(value = {ResourceVersioningController.class}, secure = false)
public class ResourceVersioningControllerTest {

    @Autowired
    private MockMvc mockMvc;

    @MockBean
    private ResourceVersioningService resourceVersioningService;

    @TestConfiguration
    @EnableAspectJAutoProxy
    public static class TestConfig {
        @Bean
        public ValidationAspect validationAspect() {
            return new ValidationAspect();
        }
    }

    @Test
    public void invalidResourceId() throws Exception {
        mockMvc.perform(get("/v1/resources/{resourceId}", "invalidId"))
                .andExpect(status().isNotFound());
    }

    @Test
    public void validResourceId() throws Exception {
        when(resourceVersioningService.findResource("123")).thenReturn(new ResourceVersions("TestSystem", "abcdefg", "http://test.test"));

        mockMvc.perform(get("/v1/resources/{resourceId}", "123"))
                .andExpect(MockMvcResultMatchers.status().isOk())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8))
                .andExpect(jsonPath("$.data.externalSystem", is("TestSystem")))
                .andExpect(jsonPath("$.data.externalReference", is("abcdefg")))
                .andExpect(jsonPath("$.data.children").isArray())
                .andExpect(jsonPath("$.data.children").isEmpty())
                .andExpect(jsonPath("$.data.parent", nullValue()));

        verify(resourceVersioningService, times(1)).findResource("123");
        verifyNoMoreInteractions(resourceVersioningService);
    }

    @Test
    public void sendInvalidStoreRequest() throws Exception {

        mockMvc.perform(post("/v1/resources"))
                .andExpect(status().isBadRequest());

        mockMvc.perform(post("/v1/resources")
                .contentType(MediaType.APPLICATION_JSON)
                .content(""))
                .andExpect(status().isBadRequest());

        mockMvc.perform(post("/v1/resources")
                .param("externalSystem", "ThisIsAFaaarTooLoooooooongExternalSystemName")
                .param("externalReference", "Resource_1")
                .param("externalUrl", "http://test.test")
                .param("coreId", "aaaabbbb"))
                .andExpect(status().isBadRequest())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8))
                .andExpect(jsonPath("$.errors").isArray())
                .andExpect(jsonPath("$.errors", hasSize(1)))
                .andExpect(jsonPath("$.errors[0].field", is("externalSystem")));

        mockMvc.perform(post("/v1/resources")
                .param("externalSystem", "UnitTest")
                .param("externalReference", "")
                .param("externalUrl", "http://test.test")
                .param("coreId", "aaaabbbb"))
                .andExpect(status().isBadRequest())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8))
                .andExpect(jsonPath("$.errors").isArray())
                .andExpect(jsonPath("$.errors", hasSize(1)))
                .andExpect(jsonPath("$.errors[0].field", is("externalReference")));

        mockMvc.perform(post("/v1/resources")
                .param("externalSystem", "UnitTest")
                .param("externalReference", "UnitRef")
                .param("externalUrl", "http://test.test")
                .param("originSystem", "ThisIsAFarTooLooooooooooooooooooooooooongOriginSystemName"))
                .andExpect(status().isBadRequest())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8))
                .andExpect(jsonPath("$.errors").isArray())
                .andExpect(jsonPath("$.errors", hasSize(1)))
                .andExpect(jsonPath("$.errors[0].field", is("originSystem")));
    }

    @Test
    public void storeWithInvalidParent() throws Exception {
        when(resourceVersioningService.storeResource(Mockito.any(ResourceVersions.class))).thenThrow(
                new NoSuchElementException("Could not find the parent element")
        );

        mockMvc.perform(post("/v1/resources")
                .param("externalSystem", "UnitTest")
                .param("externalReference", "UnitTest1")
                .param("externalUrl", "http://test.test")
                .param("parentId", "notValidParentId"))
                .andExpect(status().isBadRequest())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8))
                .andExpect(jsonPath("$.errors").isArray())
                .andExpect(jsonPath("$.errors", hasSize(1)))
                .andExpect(jsonPath("$.errors[0].message", is("Could not find the parent element")));
    }

    @Test
    public void storeResourceWithJsonWithInvalidCreatedAt_thenFail() throws Exception {

        when(resourceVersioningService.storeResource(Mockito.any(ResourceVersions.class))).thenAnswer(invocation -> {
            ResourceVersions resourceVersions = invocation.getArgument(0);
            resourceVersions.setId(UUID.randomUUID().toString());
            return resourceVersions;
        });

        JSONObject json = new JSONObject();
        json.put("externalSystem", "UnitTestJson");
        json.put("externalReference", "Resource_1_json");
        json.put("externalUrl", "http://test.me.hard");
        json.put("createdAt", "NotANumber");

        mockMvc.perform(post("/v1/resources")
                .contentType(MediaType.APPLICATION_JSON)
                .content(json.toString())
                .accept(MediaType.APPLICATION_JSON))
                .andExpect(status().isCreated())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8))
                .andExpect(jsonPath("$.data.id", notNullValue()))
                .andExpect(jsonPath("$.data.externalSystem", is("UnitTestJson")))
                .andExpect(jsonPath("$.data.createdAt", nullValue()))
                .andExpect(jsonPath("$.data.externalReference", is("Resource_1_json")));
    }

    @Test
    public void storeResourceWithJsonWithValidCreatedAt_thenSuccess() throws Exception {


        when(resourceVersioningService.storeResource(Mockito.any(ResourceVersions.class))).thenAnswer(invocation -> {
            ResourceVersions resourceVersions = invocation.getArgument(0);
            resourceVersions.setId(UUID.randomUUID().toString());
            return resourceVersions;
        });

        Date createdAt = Date.from(Instant.now().minus(1, ChronoUnit.DAYS));

        JSONObject json = new JSONObject();
        json.put("externalSystem", "UnitTestJson");
        json.put("externalReference", "Resource_1_json");
        json.put("externalUrl", "http://test.me.hard");
        json.put("createdAt", createdAt.toInstant().getEpochSecond());

        mockMvc.perform(post("/v1/resources")
                .contentType(MediaType.APPLICATION_JSON)
                .content(json.toString())
                .accept(MediaType.APPLICATION_JSON))
                .andExpect(status().isCreated())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8))
                .andExpect(jsonPath("$.data.id", notNullValue()))
                .andExpect(jsonPath("$.data.externalSystem", is("UnitTestJson")))
                .andExpect(jsonPath("$.data.createdAt", is(createdAt.toInstant().getLong(ChronoField.INSTANT_SECONDS) * 1000)))
                .andExpect(jsonPath("$.data.externalReference", is("Resource_1_json")));
    }

    @Test
    public void testLinearVersioningError() throws Exception {
        ResourceVersions parentVersion = new ResourceVersions("ContentAuthor", "1", null);
        parentVersion.setId(UUID.randomUUID().toString());
        parentVersion.setCreatedAt(new Date());
        ResourceVersions leafVersion = new ResourceVersions("ContentAuthor", "1", null);
        leafVersion.setId(UUID.randomUUID().toString());
        leafVersion.setCreatedAt(new Date());
        when(resourceVersioningService.storeResource(Mockito.any(ResourceVersions.class))).thenThrow(new ResourceVersioningService.ResourceVersioningLinearVersioningException(parentVersion));
        given(resourceVersioningService.getLeafs(parentVersion.getId())).willReturn(List.of(leafVersion));

        mockMvc.perform(post("/v1/resources")
                .param("externalSystem", "UnitTest")
                .param("externalReference", "Resource_1")
                .param("externalUrl", "http://test.test")
                .param("coreId", "aaaabbbb"))
                .andExpect(status().isConflict())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8))
                .andExpect(jsonPath("$.requestedParent.id", is(parentVersion.getId())))
                .andExpect(jsonPath("$.leafs[0].id", is(leafVersion.getId())));

        verify(resourceVersioningService, times(1)).storeResource(Mockito.any(ResourceVersions.class));
        verify(resourceVersioningService, times(1)).getLeafs(parentVersion.getId());
        verifyNoMoreInteractions(resourceVersioningService);
    }

    @Test
    public void sendValidRequest() throws Exception {
        when(resourceVersioningService.storeResource(Mockito.any(ResourceVersions.class))).thenAnswer(invocation -> {
            ResourceVersions resourceVersions = invocation.getArgument(0);
            resourceVersions.setId(UUID.randomUUID().toString());
            resourceVersions.setCreatedAt(new Date());
            return resourceVersions;
        });

        mockMvc.perform(post("/v1/resources")
                .param("externalSystem", "UnitTest")
                .param("externalReference", "Resource_1")
                .param("externalUrl", "http://test.test")
                .param("coreId", "aaaabbbb"))
                .andExpect(status().isCreated())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8))
                .andExpect(jsonPath("$.data.coreId", is("aaaabbbb")))
                .andExpect(jsonPath("$.data.id", notNullValue()))
                .andExpect(jsonPath("$.data.externalSystem", is("UnitTest")));

        verify(resourceVersioningService, times(1)).storeResource(Mockito.any(ResourceVersions.class));
        verifyNoMoreInteractions(resourceVersioningService);
    }

    @Test
    public void sendInvalidRequest() throws Exception {
        mockMvc.perform(post("/v1/resources"))
                .andExpect(status().isBadRequest());

    }

    @Test
    public void InvalidUpdateFromCore() throws Exception {

        mockMvc.perform(put("/v1/resources/core/{exSystem}/{exId}", "ThisIsALongNameForAnExternalSystemPleaseShortenIt", "8888")
                .param("coreId", "abcdefg"))
                .andExpect(status().isBadRequest())
                .andExpect(jsonPath("$.type", is("failure")))
                .andExpect(jsonPath("$.errors").isArray())
                .andExpect(jsonPath("$.errors", hasSize(1)));
    }

    @Test
    public void updateResourceFromCore() throws Exception {
        when(resourceVersioningService.updateResourceWithCoreData("TestContentAuthor", "123", "1111", "http://core.url")).thenAnswer(invocation -> {
            ResourceVersions resourceVersions = new ResourceVersions("TestContentAuthor", "123", "http://core.url");
            resourceVersions.setCoreId("1111");
            return resourceVersions;
        });

        when(resourceVersioningService.updateResourceWithCoreData("TestContentAuthor", "321", "1111", null)).thenThrow(
                new ResourceNotFoundException("TestContentAuthor - 321")
        );

        mockMvc.perform(put("/v1/resources/core/{exSystem}/{exId}", "TestContentAuthor", "123")
                .param("coreId", "1111")
                .param("externalUrl", "http://core.url"))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.type", is("success")))
                .andExpect(jsonPath("$.data.coreId", is("1111")))
                .andExpect(jsonPath("$.data.externalUrl", is("http://core.url")))
                .andExpect(jsonPath("$.errors").isArray())
                .andExpect(jsonPath("$.errors", hasSize(0)));

        mockMvc.perform(put("/v1/resources/core/{exSystem}/{exId}", "TestContentAuthor", "321")
                .param("coreId", "1111"))
                .andExpect(status().isNotFound())
                .andExpect(jsonPath("$.errors").isArray())
                .andExpect(jsonPath("$.errors", hasSize(1)))
                .andExpect(jsonPath("$.errors[0].message", is("The resource 'TestContentAuthor - 321' was not found.")));
    }

    @Test
    public void findOriginResource() throws Exception {
        when(resourceVersioningService.findOriginResources("NDLA_test", "ndla_node"))
                .thenAnswer(invocation -> new ResourceVersions("VersionTest", "version_1", "http://test.test", "import", "NDLA_test", "ndla_node"));

        when(resourceVersioningService.findOriginResources("CERPUS", "555")).thenThrow(
                new ResourceNotFoundException("CERPUS - 555")
        );

        when(resourceVersioningService.findOriginResources("NDLA", "1234"))
                .thenAnswer(invocation -> {
                    ResourceVersions parent = new ResourceVersions("VersionTest", "version_1", "http://test.test", "import", "NDLA", "1234");
                    parent.setId("parentId_1");

                    ArrayList children = new ArrayList();

                    ResourceVersions child = new ResourceVersions("VersionTest", "version_2", "http://test.test", "modified", parent.getId());
                    child.setId("childId_1");
                    children.add(child);

                    child = new ResourceVersions("VersionTest", "version_3", "http://test.test", "modified", parent.getId());
                    child.setId("childId_2");

                    children.add(child);
                    parent.setChildren(children);

                    return parent;
                });

        mockMvc.perform(get("/v1/resources/origin/CERPUS/555"))
                .andExpect(status().isNotFound())
                .andExpect(jsonPath("$.errors", hasSize(1)))
                .andExpect(jsonPath("$.type", is("failure")));

        mockMvc.perform(get("/v1/resources/origin/NDLA_test/ndla_node"))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.data.versionPurpose", is("import")))
                .andExpect(jsonPath("$.data.externalSystem", is("VersionTest")));

        mockMvc.perform(get("/v1/resources/origin/NDLA/1234"))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.data.id", is("parentId_1")))
                .andExpect(jsonPath("$.data.parent").doesNotExist())
                .andExpect(jsonPath("$.data.children", hasSize(2)))
                .andExpect(jsonPath("$.data.children[0].id", is("childId_1")));
    }


    @Test
    public void findLatestResource() throws Exception {
        when(resourceVersioningService.findLatestVersion("validId"))
                .thenAnswer(invocation -> {
                    ResourceVersions resource = new ResourceVersions("UNIT_TEST", "external_latest", "http://test.me");
                    resource.setId("validId");
                    return resource;
                });

        when(resourceVersioningService.findLatestVersion("invalidId"))
                .thenThrow(new NoSuchElementException("Invalid ID"));


        mockMvc.perform(get("/v1/resources/validId/latest"))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.data.id", is("validId")))
                .andExpect(jsonPath("$.data.externalSystem", is("UNIT_TEST")))
                .andExpect(jsonPath("$.data.parent").doesNotExist())
                .andExpect(jsonPath("$.errors", hasSize(0)));

        mockMvc.perform(get("/v1/resources/invalidId/latest"))
                .andExpect(status().isBadRequest())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8))
                .andExpect(jsonPath("$.errors", hasSize(1)))
                .andExpect(jsonPath("$.errors[0].message", is("Invalid ID")));
    }

    @Test
    public void findResourceFromExternalProperties() throws Exception {
        when(resourceVersioningService.findResourceByExternalProperties("ValidExternalSystem", "validExternalId"))
                .thenAnswer(invocation -> {
                    ResourceVersions resource = new ResourceVersions("ValidExternalSystem", "validExternalId", "http://test.me");
                    return resource;
                });

        when(resourceVersioningService.findResourceByExternalProperties("InvalidExternalSystem", "invalidExternalId"))
                .thenThrow(new ResourceNotFoundException("InvalidExternalSystem - invalidExternalId"));


        mockMvc.perform(get("/v1/resources/ValidExternalSystem/validExternalId"))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.data.externalSystem", is("ValidExternalSystem")))
                .andExpect(jsonPath("$.data.externalReference", is("validExternalId")))
                .andExpect(jsonPath("$.data.parent").doesNotExist())
                .andExpect(jsonPath("$.errors", hasSize(0)));

        mockMvc.perform(get("/v1/resources/InvalidExternalSystem/invalidExternalId"))
                .andExpect(status().isNotFound())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8))
                .andExpect(jsonPath("$.errors", hasSize(1)))
                .andExpect(jsonPath("$.errors[0].message", is("The resource 'InvalidExternalSystem - invalidExternalId' was not found.")));
    }

}
