package no.cerpus.versioning

import no.cerpus.versioning.models.ResourceVersions
import java.time.Instant
import java.util.*

private var serialH5pNum = 0;

/*
 * Resource version tree builder which is supposed to make it easier to
 * get the structure when looking at the test code:
 */
class ResourceTreeBuilder(val id: String? = UUID.randomUUID().toString(), val versionPurpose: String = "create", val externalSystem: String = "ContentAuthor", val externalReference: String = ""+(++serialH5pNum), val externalUrl: String = "http://h5p.cerpus.com/"+ serialH5pNum, builder: (ResourceTreeBuilder) -> Unit = { }) {
    val children = ArrayList<ResourceTreeBuilder>();
    var created: Instant = Instant.now()

    init {
        builder(this@ResourceTreeBuilder)
    }

    fun childVersion(id: String? = UUID.randomUUID().toString(), versionPurpose: String = "edit", builder: (ResourceTreeBuilder) -> Unit = { }): ResourceTreeBuilder {
        val builder = ResourceTreeBuilder(
                id = id,
                versionPurpose = versionPurpose,
                builder = builder
        );
        children.add(builder)
        return builder
    }

    fun build(): ResourceVersions {
        val childResources = children.map { builder -> builder.build() }
        childResources.forEach { child ->
            child.parent = id
        }
        val ver = ResourceVersions(externalSystem, externalReference, externalUrl)
        ver.id = id
        ver.versionPurpose = versionPurpose
        ver.children = childResources
        ver.createdAt = Date(created.toEpochMilli())
        return ver
    }
}
