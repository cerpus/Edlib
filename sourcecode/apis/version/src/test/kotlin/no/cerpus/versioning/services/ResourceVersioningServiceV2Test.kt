package no.cerpus.versioning.services

import no.cerpus.versioning.ResourceTreeBuilder
import no.cerpus.versioning.repository.ResourceVersionRepository
import org.junit.Assert.assertEquals
import org.junit.Assert.assertNotNull
import org.junit.Before
import org.junit.Test
import org.mockito.BDDMockito.given
import org.mockito.Mockito.mock
import org.mockito.Mockito.reset
import java.time.Instant
import java.util.*

class ResourceVersioningServiceV2Test {
    val resourceVersionRepository: ResourceVersionRepository = mock<ResourceVersionRepository>(ResourceVersionRepository::class.java)
    val resourceVersioningService: ResourceVersioningServiceImpl = ResourceVersioningServiceImpl()

    init {
        resourceVersioningService.setResourceVersionRepository(resourceVersionRepository)
    }

    @Before
    fun beforeTesting() {
        reset(resourceVersionRepository)
    }

    @Test
    fun testGetLatest() {
        /*
         * Build a tree of resources with different created dates
         */
        var builder = ResourceTreeBuilder(id = "ef74c6af-636a-4f98-8d6f-2e2541f4c80b") {
            it.created = Instant.ofEpochSecond(0)

            it.childVersion {
                it.created = Instant.ofEpochSecond(3)

                it.childVersion {
                    it.created = Instant.ofEpochSecond(2)
                }
                it.childVersion {
                    it.created = Instant.ofEpochSecond(3)
                }
            }

            it.childVersion {
                it.created = Instant.ofEpochSecond(1)

                it.childVersion {
                    it.created = Instant.ofEpochSecond(3)
                }
                /*
                 * This is what we expect the "find latest" algorithm to find:
                 */
                it.childVersion(id = "0608f4a6-2284-4640-8ee5-891aecd42e46") {
                    it.created = Instant.ofEpochSecond(4)
                }
            }

            it.childVersion {
                it.created = Instant.ofEpochSecond(2)

                it.childVersion {
                    it.created = Instant.ofEpochSecond(2)
                }
            }
        }

        var rootResource = builder.build()
        assertEquals("ef74c6af-636a-4f98-8d6f-2e2541f4c80b", rootResource.id); // Self test
        assertEquals(Instant.ofEpochSecond(0), rootResource.createdAt.toInstant()) // Self test
        given(resourceVersionRepository.findById(rootResource.id)).willReturn(Optional.of(rootResource))

        /*
         * The algorithm is supposed to find the most recent leaf node
         */
        val latestVersion = resourceVersioningService.findLatestVersion(rootResource.id)
        assertNotNull(latestVersion)
        assertEquals("0608f4a6-2284-4640-8ee5-891aecd42e46", latestVersion.id)
        assertEquals(Instant.ofEpochSecond(4), latestVersion.createdAt.toInstant())
    }
}