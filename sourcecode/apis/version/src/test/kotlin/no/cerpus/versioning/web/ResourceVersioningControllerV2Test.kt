package no.cerpus.versioning.web

import no.cerpus.versioning.Constants
import no.cerpus.versioning.ResourceTreeBuilder
import no.cerpus.versioning.aspects.ValidationAspect
import no.cerpus.versioning.services.ResourceVersioningService
import org.hamcrest.Matchers.`is`
import org.junit.Test
import org.junit.runner.RunWith
import org.mockito.BDDMockito.given
import org.springframework.beans.factory.annotation.Autowired
import org.springframework.boot.test.autoconfigure.web.servlet.WebMvcTest
import org.springframework.boot.test.context.TestConfiguration
import org.springframework.boot.test.mock.mockito.MockBean
import org.springframework.context.annotation.Bean
import org.springframework.context.annotation.EnableAspectJAutoProxy
import org.springframework.test.context.junit4.SpringRunner
import org.springframework.test.web.servlet.MockMvc
import org.springframework.test.web.servlet.request.MockMvcRequestBuilders.get
import org.springframework.test.web.servlet.result.MockMvcResultMatchers.jsonPath
import org.springframework.test.web.servlet.result.MockMvcResultMatchers.status
import org.springframework.web.util.UriTemplate
import java.lang.NullPointerException

@RunWith(SpringRunner::class)
@WebMvcTest(value = [ResourceVersioningController::class], secure = false)
class ResourceVersioningControllerV2Test {
    @Autowired
    private val mockMvc: MockMvc? = null

    @MockBean
    private val resourceVersioningService: ResourceVersioningService? = null

    @TestConfiguration
    @EnableAspectJAutoProxy
    open class TestConfig {
        @Bean
        open fun validationAspect(): ValidationAspect {
            return ValidationAspect()
        }
    }

    @Test
    @Throws(Exception::class)
    fun getLatest_thenSuccess() {
        val resourceBuilder = ResourceTreeBuilder(id = "0a3989dd-4466-4689-8dd5-e937011773f9")
        val uriTemplate = UriTemplate(Constants.API_VERSION + Constants.API_PREFIX + Constants.API_LATEST)
        val uri = uriTemplate.expand("d17b2deb-81f7-48a6-85b8-4ceb5881c09d")

        given(resourceVersioningService?.findLatestVersion("d17b2deb-81f7-48a6-85b8-4ceb5881c09d")).willReturn(resourceBuilder.build());

        mockMvc?.perform(get(uri.toString()))
                ?.andExpect(status().isOk())
                ?.andExpect(jsonPath("$.data.id", `is`("0a3989dd-4466-4689-8dd5-e937011773f9")))
                ?: throw NullPointerException("MockMvc N/A")
    }
}