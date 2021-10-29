
package no.cerpus.versioning.web;


import no.cerpus.versioning.Constants;
import no.cerpus.versioning.ResourceVersionData;
import no.cerpus.versioning.models.ResourceVersions;
import org.json.JSONArray;
import org.json.JSONObject;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.autoconfigure.web.servlet.AutoConfigureMockMvc;
import org.springframework.boot.test.autoconfigure.web.servlet.WebMvcTest;
import org.springframework.boot.test.context.SpringBootTest;
import org.springframework.format.annotation.DateTimeFormat;
import org.springframework.http.HttpMethod;
import org.springframework.http.MediaType;
import org.springframework.mock.web.MockHttpServletRequest;
import org.springframework.test.context.junit4.SpringRunner;
import org.springframework.test.context.web.WebAppConfiguration;
import org.springframework.test.web.servlet.MockMvc;
import org.springframework.test.web.servlet.MvcResult;
import org.springframework.util.LinkedMultiValueMap;
import org.springframework.util.MultiValueMap;
import org.springframework.web.bind.annotation.RequestMethod;
import org.springframework.web.util.UriTemplate;

import java.net.URI;
import java.time.Instant;
import java.time.temporal.ChronoField;
import java.time.temporal.ChronoUnit;
import java.util.Date;

import static org.hamcrest.Matchers.*;
import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertNull;
import static org.junit.Assert.assertTrue;
import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.*;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.*;

@RunWith(SpringRunner.class)
@SpringBootTest(webEnvironment = SpringBootTest.WebEnvironment.MOCK)
@AutoConfigureMockMvc
public class ResourceVersioningIntegrationTest extends ResourceVersionData {

    @Autowired
    private MockMvc mockMvc;

    @Test
    public void getResource_thenSuccess() throws Exception {
        ResourceVersions resource = getRandomLocalResource();
        UriTemplate uriTemplate = new UriTemplate(Constants.API_VERSION + Constants.API_PREFIX + "/{id}");
        URI url = uriTemplate.expand(resource.getId());
        MvcResult result = mockMvc.perform(get(url.toString()))
                .andExpect(status().isOk())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8))
                .andExpect(jsonPath("$.data.id", is(resource.getId())))
                .andExpect(jsonPath("$.data.createdAt", is(resource.getCreatedAt().toInstant().toEpochMilli())))
                .andExpect(jsonPath("$.data.externalReference", is(resource.getExternalReference())))
                .andExpect(jsonPath("$.data.externalSystem", is(resource.getExternalSystem())))
                .andReturn();

        MockHttpServletRequest request = result.getRequest();
        assertEquals(request.getMethod(), RequestMethod.GET.toString());
    }


    @Test
    public void getResource_thenFail() throws Exception {
        UriTemplate uriTemplate = new UriTemplate(Constants.API_VERSION + Constants.API_PREFIX + "/{id}");
        URI url = uriTemplate.expand("notValid");
        mockMvc.perform(get(url.toString()))
                .andExpect(status().isNotFound())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8))
                .andExpect(jsonPath("$.type", is("failure")))
                .andExpect(jsonPath("$.message", is("The request failed")))
                .andExpect(jsonPath("$.errors[0].code", nullValue()))
                .andExpect(jsonPath("$.errors[0].message", is("The resource 'notValid' was not found.")));
    }

    @Test
    public void storeResourceViaPost_thenFail() throws Exception {
        UriTemplate uriTemplate = new UriTemplate(Constants.API_VERSION + Constants.API_PREFIX);

        MultiValueMap<String, String> params = new LinkedMultiValueMap<String, String>();
        params.set("externalSystem", "UnitIntegrationTest");

        MvcResult result = mockMvc.perform(post(uriTemplate.toString())
                .params(params)
                .header("Authorization", "Bearer accesstoken"))
                .andExpect(status().isBadRequest()).andReturn();

        assertEquals(result.getResponse().getErrorMessage(), "Parameter conditions \"externalSystem, externalReference, externalUrl\" not met for actual request parameters: externalSystem={UnitIntegrationTest}");
    }

    @Test
    public void storeResourceViaPost_thenSuccess() throws Exception {
        UriTemplate uriTemplate = new UriTemplate(Constants.API_VERSION + Constants.API_PREFIX);

        MultiValueMap<String, String> params = new LinkedMultiValueMap<String, String>();
        params.set("externalSystem", "UnitIntegrationTest");
        params.set("externalReference", "unitIntegration_1");
        params.set("externalUrl", "http://integration.test");
        params.set("versionPurpose", "create");
        params.set("user_id", "user1");

        mockMvc.perform(post(uriTemplate.toString())
                .params(params)
                .header("Authorization", "Bearer accesstoken"))
                .andExpect(status().isCreated())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8_VALUE))
                .andExpect(jsonPath("$.type", is("success")))
                .andExpect(jsonPath("$.errors", hasSize(0)))
                .andExpect(jsonPath("$.data.id", notNullValue()))
                .andExpect(jsonPath("$.data.externalSystem", is(params.getFirst("externalSystem"))))
                .andExpect(jsonPath("$.data.externalReference", is(params.getFirst("externalReference"))))
                .andExpect(jsonPath("$.data.externalUrl", is(params.getFirst("externalUrl"))))
                .andExpect(jsonPath("$.data.versionPurpose", is(params.getFirst("versionPurpose"))));
    }

    @Test
    public void storeResourceViaPostWithInvalidCreatedAt_thenFail() throws Exception {
        UriTemplate uriTemplate = new UriTemplate(Constants.API_VERSION + Constants.API_PREFIX);

        MultiValueMap<String, String> params = new LinkedMultiValueMap<String, String>();
        params.set("externalSystem", "UnitIntegrationTest");
        params.set("externalReference", "unitIntegration_1");
        params.set("externalUrl", "http://integration.test");
        params.set("versionPurpose", "create");
        params.set("user_id", "user1");
        params.set("createdAt", "notvalid");

        mockMvc.perform(post(uriTemplate.toString())
                .params(params)
                .header("Authorization", "Bearer accesstoken"))
                .andExpect(status().isBadRequest())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8_VALUE))
                .andExpect(jsonPath("$.type", is("failure")))
                .andExpect(jsonPath("$.errors[0].code", is("typeMismatch")))
                .andExpect(jsonPath("$.errors[0].message", startsWith("Failed to convert property value of type 'java.lang.String' to required type 'java.util.Date' for property 'createdAt';")));
    }

    @Test
    public void storeResourceViaPostWithValidCreatedAt_thenSuccess() throws Exception {
        UriTemplate uriTemplate = new UriTemplate(Constants.API_VERSION + Constants.API_PREFIX);

        Instant createdAt = Instant.now().plus(1, ChronoUnit.DAYS);

        MultiValueMap<String, String> params = new LinkedMultiValueMap<String, String>();
        params.set("externalSystem", "UnitIntegrationTest");
        params.set("externalReference", "unitIntegration_1");
        params.set("externalUrl", "http://integration.test");
        params.set("versionPurpose", "create");
        params.set("user_id", "user1");
        params.set("createdAt", createdAt.toString());

        MvcResult result = mockMvc.perform(post(uriTemplate.toString())
                .params(params)
                .header("Authorization", "Bearer accesstoken"))
                .andExpect(status().isCreated())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8_VALUE))
                .andExpect(jsonPath("$.type", is("success")))
                .andExpect(jsonPath("$.data.id", notNullValue()))
                .andReturn();

        String content = result.getResponse().getContentAsString();
        JSONObject json = new JSONObject(content);
        Instant returnedCreatedAt = Instant.ofEpochMilli((long) json.getJSONObject("data").get("createdAt"));
        assertTrue(createdAt.isAfter(returnedCreatedAt));
        createdAt = createdAt.minus(1, ChronoUnit.DAYS);
        assertEquals(returnedCreatedAt.truncatedTo(ChronoUnit.DAYS), createdAt.truncatedTo(ChronoUnit.DAYS));
    }

    @Test
    public void storeResourceViaJSONPost_thenFail() throws Exception {
        UriTemplate uriTemplate = new UriTemplate(Constants.API_VERSION + Constants.API_PREFIX);

        JSONObject json = new JSONObject();
        json.put("externalSystem", "UnitIntegrationTest");

        mockMvc.perform(post(uriTemplate.toString())
                .contentType(MediaType.APPLICATION_JSON)
                .content(json.toString())
                .accept(MediaType.APPLICATION_JSON)
                .header("Authorization", "Bearer accesstoken"))
                .andExpect(status().isBadRequest())
                .andExpect(jsonPath("$.data", nullValue()))
                .andExpect(jsonPath("$.type", is("failure")))
                .andExpect(jsonPath("$.errors", hasSize(2)))
                .andExpect(jsonPath("$.errors[*]", hasItems(
                        hasEntry("code", "NotNull"),
                        hasEntry("code", "NotEmpty")
                )))
                .andExpect(jsonPath("$.message", is("The request had invalid properties.")));
    }

    @Test
    public void storeResourceViaJSONPost_thenSuccess() throws Exception {
        UriTemplate uriTemplate = new UriTemplate(Constants.API_VERSION + Constants.API_PREFIX);

        JSONObject json = new JSONObject();
        json.put("externalSystem", "UnitIntegrationTest");
        json.put("externalReference", "unitIntegration_1");
        json.put("externalUrl", "http://integration.test");

        mockMvc.perform(post(uriTemplate.toString())
                .contentType(MediaType.APPLICATION_JSON)
                .header("Authorization", "Bearer accesstoken")
                .content(json.toString())
                .accept(MediaType.APPLICATION_JSON))
                .andExpect(status().isCreated())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8_VALUE))
                .andExpect(jsonPath("$.type", is("success")))
                .andExpect(jsonPath("$.errors", hasSize(0)))
                .andExpect(jsonPath("$.data.id", notNullValue()))
                .andExpect(jsonPath("$.data.externalSystem", is(json.get("externalSystem"))))
                .andExpect(jsonPath("$.data.externalReference", is(json.get("externalReference"))))
                .andExpect(jsonPath("$.data.externalUrl", is(json.get("externalUrl"))))
                .andExpect(jsonPath("$.data.versionPurpose", is("create")));
    }

    @Test
    public void storeResourceViaJSONPostWithValidCreatedAt_thenSuccess() throws Exception {
        UriTemplate uriTemplate = new UriTemplate(Constants.API_VERSION + Constants.API_PREFIX);

        Instant createdAt = Instant.now().minus(1, ChronoUnit.DAYS);

        JSONObject json = new JSONObject();
        json.put("externalSystem", "UnitIntegrationTest");
        json.put("externalReference", "unitIntegration_1");
        json.put("externalUrl", "http://integration.test");
        json.put("versionPurpose", "create");
        json.put("createdAt", createdAt.toString());

        MvcResult result = mockMvc.perform(post(uriTemplate.toString())
                .contentType(MediaType.APPLICATION_JSON)
                .header("Authorization", "Bearer accesstoken")
                .content(json.toString())
                .accept(MediaType.APPLICATION_JSON))
                .andExpect(status().isCreated())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8))
                .andExpect(jsonPath("$.data.id", notNullValue()))
                .andExpect(jsonPath("$.data.externalSystem", is("UnitIntegrationTest")))
                .andExpect(jsonPath("$.data.externalReference", is("unitIntegration_1")))
                .andReturn();

        String content = result.getResponse().getContentAsString();
        JSONObject responseJSON = new JSONObject(content);
        Instant returnedCreatedAt = Instant.ofEpochMilli((long) responseJSON.getJSONObject("data").get("createdAt"));
        assertTrue(createdAt.isBefore(returnedCreatedAt));
        createdAt = createdAt.plus(1, ChronoUnit.DAYS);
        assertEquals(returnedCreatedAt.truncatedTo(ChronoUnit.DAYS), createdAt.truncatedTo(ChronoUnit.DAYS));
    }

    @Test
    public void initialStoreResourceViaJSONPostWithValidCreatedAtAsISODate_thenSuccess() throws Exception {
        UriTemplate uriTemplate = new UriTemplate(Constants.API_VERSION + Constants.API_PREFIX);

        Instant createdAt = Instant.now().minus(2, ChronoUnit.DAYS);

        JSONObject json = new JSONObject();
        json.put("externalSystem", "UnitIntegrationTest");
        json.put("externalReference", "unitIntegration_1");
        json.put("externalUrl", "http://integration.test");
        json.put("versionPurpose", "initial");
        json.put("createdAt", createdAt.toString());

        MvcResult result = mockMvc.perform(post(uriTemplate.toString())
                .contentType(MediaType.APPLICATION_JSON)
                .header("Authorization", "Bearer accesstoken")
                .content(json.toString())
                .accept(MediaType.APPLICATION_JSON))
                .andExpect(status().isCreated())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8))
                .andExpect(jsonPath("$.data.id", notNullValue()))
                .andExpect(jsonPath("$.data.externalSystem", is(json.get("externalSystem"))))
                .andExpect(jsonPath("$.data.externalReference", is(json.get("externalReference"))))
                .andExpect(jsonPath("$.data.versionPurpose", is(json.get("versionPurpose"))))
                .andReturn();

        JSONObject responseJSON = new JSONObject(result.getResponse().getContentAsString());
        JSONObject responseData = responseJSON.getJSONObject("data");

        Instant returnedCreatedAt = Instant.ofEpochMilli((long) responseData.get("createdAt"));
        // Equals check with rounding/conversion error tolerance of +-1sec:
        assertTrue(Math.abs(createdAt.getEpochSecond() - returnedCreatedAt.getEpochSecond()) <= 1);

        StringBuilder redirectUrl = new StringBuilder()
                .append(uriTemplate.toString())
                .append("/")
                .append(responseData.get("id"));
        assertEquals(result.getResponse().getRedirectedUrl(), redirectUrl.toString());
    }

    @Test
    public void initialStoreResourceViaJSONPostWithValidCreatedAtAsTimestamp_thenSuccess() throws Exception {
        UriTemplate uriTemplate = new UriTemplate(Constants.API_VERSION + Constants.API_PREFIX);

        Instant createdAt = Instant.now().minus(2, ChronoUnit.DAYS);

        JSONObject json = new JSONObject();
        json.put("externalSystem", "UnitIntegrationTest");
        json.put("externalReference", "unitIntegration_1");
        json.put("externalUrl", "http://integration.test");
        json.put("versionPurpose", "initial");
        json.put("createdAt", createdAt.getEpochSecond());

        MvcResult result = mockMvc.perform(post(uriTemplate.toString())
                .contentType(MediaType.APPLICATION_JSON)
                .header("Authorization", "Bearer accesstoken")
                .content(json.toString())
                .accept(MediaType.APPLICATION_JSON))
                .andExpect(status().isCreated())
                .andExpect(content().contentType(MediaType.APPLICATION_JSON_UTF8))
                .andExpect(jsonPath("$.data.id", notNullValue()))
                .andExpect(jsonPath("$.data.externalSystem", is(json.get("externalSystem"))))
                .andExpect(jsonPath("$.data.externalReference", is(json.get("externalReference"))))
                .andExpect(jsonPath("$.data.versionPurpose", is(json.get("versionPurpose"))))
                .andExpect(jsonPath("$.data.createdAt", is(createdAt.truncatedTo(ChronoUnit.SECONDS).toEpochMilli())))
                .andReturn();

        JSONObject responseData = new JSONObject(result.getResponse().getContentAsString()).getJSONObject("data");

        StringBuilder redirectUrl = new StringBuilder()
                .append(uriTemplate.toString())
                .append("/")
                .append(responseData.get("id"));
        assertEquals(result.getResponse().getRedirectedUrl(), redirectUrl.toString());
    }

    @Test
    public void updateResourceFromCore_thenSuccess() throws Exception {
        ResourceVersions resourceVersions = getRandomLocalResource();
        UriTemplate uriTemplate = new UriTemplate(Constants.API_VERSION + Constants.API_PREFIX + Constants.API_CORE);
        URI url = uriTemplate.expand(resourceVersions.getExternalSystem(), resourceVersions.getExternalReference());

        assertNull(resourceVersions.getCoreId());

        MultiValueMap<String, String> params = new LinkedMultiValueMap<String, String>();
        params.set("coreId", "core_id_1");

        mockMvc.perform(put(url.toString())
                .params(params)
                .header("Authorization", "Bearer accesstoken"))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.data.id", is(resourceVersions.getId())))
                .andExpect(jsonPath("$.data.coreId", notNullValue()))
                .andExpect(jsonPath("$.data.externalUrl", is(resourceVersions.getExternalUrl())));

        params.set("externalUrl", "http://integration.test");
        params.set("coreId", "core_id_2");

        mockMvc.perform(put(url.toString())
                .params(params)
                .header("Authorization", "Bearer accesstoken"))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.data.id", is(resourceVersions.getId())))
                .andExpect(jsonPath("$.data.coreId", not("core_id_1")))
                .andExpect(jsonPath("$.data.coreId", is("core_id_2")))
                .andExpect(jsonPath("$.data.externalUrl", is("http://integration.test")));
    }

    @Test
    public void getLatest_thenFail() throws Exception {
        UriTemplate uriTemplate = new UriTemplate(Constants.API_VERSION + Constants.API_PREFIX + Constants.API_LATEST);
        URI uri = uriTemplate.expand("notValid");

        mockMvc.perform(get(uri.toString()))
                .andExpect(status().isBadRequest());
    }

    @Test
    public void getByExternalValues_thenSuccess() throws Exception {
        ResourceVersions resource = getRandomLocalResource();
        UriTemplate uriTemplate = new UriTemplate(Constants.API_VERSION + Constants.API_PREFIX + Constants.API_EXTERNAL);
        URI uri = uriTemplate.expand(resource.getExternalSystem(), resource.getExternalReference());

        mockMvc.perform(get(uri.toString()))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.data.id", is(resource.getId())));
    }

    @Test
    public void getByExternalValues_thenFail() throws Exception {
        UriTemplate uriTemplate = new UriTemplate(Constants.API_VERSION + Constants.API_PREFIX + Constants.API_EXTERNAL);
        URI uri = uriTemplate.expand("just", "kidding");

        mockMvc.perform(get(uri.toString()))
                .andExpect(status().isNotFound());
    }

}
