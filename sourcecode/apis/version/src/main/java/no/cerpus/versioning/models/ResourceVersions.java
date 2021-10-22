package no.cerpus.versioning.models;

import com.fasterxml.jackson.annotation.*;
import com.fasterxml.jackson.core.JsonParser;
import com.fasterxml.jackson.core.JsonProcessingException;
import com.fasterxml.jackson.databind.DeserializationContext;
import com.fasterxml.jackson.databind.JsonDeserializer;
import com.fasterxml.jackson.databind.annotation.JsonDeserialize;
import no.cerpus.versioning.views.Views;
import org.apache.commons.lang.builder.EqualsBuilder;
import org.hibernate.annotations.GenericGenerator;
import org.hibernate.validator.constraints.NotEmpty;
import org.hibernate.validator.constraints.URL;
import org.springframework.format.annotation.DateTimeFormat;

import javax.persistence.*;
import javax.validation.constraints.NotNull;
import javax.validation.constraints.Size;
import javax.validation.groups.Default;
import java.io.IOException;
import java.time.Instant;
import java.time.format.DateTimeParseException;
import java.util.Collection;
import java.util.Collections;
import java.util.Date;
import java.util.Optional;

@Entity
@Table(name = "resource_versions")
public class ResourceVersions {

    public boolean isLinearVersioning() {
        return linearVersioning;
    }

    public void setLinearVersioning(boolean linearVersioning) {
        this.linearVersioning = linearVersioning;
    }

    public static class UnitTimestampDeserializer extends JsonDeserializer<Date> {

        @Override
        public Date deserialize(JsonParser jsonParser, DeserializationContext deserializationContext) throws IOException, JsonProcessingException {
            try {
                Long timestamp = jsonParser.getValueAsLong();
                if (timestamp != 0) {
                    return Date.from(Instant.ofEpochSecond(timestamp));
                }

                return Date.from(Instant.parse(jsonParser.getText()));
            } catch (NumberFormatException e) {
                return null;
            } catch (DateTimeParseException e) {
                return null;
            }
        }
    }

    public interface UpdateFromCoreValidation {
        // validation group marker
    }

    public static class PublicView extends Views.Public {
    }

    public static class OriginView extends PublicView {
    }

    public static class InternalView extends PublicView {
    }


    @JsonView(PublicView.class)
    @Id
    @GeneratedValue(generator = "system-uuid")
    @GenericGenerator(name = "system-uuid", strategy = "uuid2")
    private String id;

    @JsonView(PublicView.class)
    @Size(min = 1, max = 40, message = "Must be between 1 and 40 characters long", groups = {Default.class, UpdateFromCoreValidation.class})
    @NotNull
    @Column(name = "external_system")
    private String externalSystem;

    @JsonView(PublicView.class)
    @Size(min = 1, max = 100, message = "Must be between 1 and 100 characters long", groups = {Default.class, UpdateFromCoreValidation.class})
    @NotNull
    @Column(name = "external_reference")
    private String externalReference;

    @JsonView(PublicView.class)
    @NotEmpty(groups = {Default.class})
    @URL
    @Column(name = "external_url")
    private String externalUrl;

    @JsonView(InternalView.class)
    @JsonIgnoreProperties({"children"})
    @JoinColumn(name = "parent_id", updatable = false, insertable = false)
    @ManyToOne
    private ResourceVersions parentVersion;

    @JsonIgnore
    @Column(name = "parent_id")
    private String parent;

    @JsonView(PublicView.class)
    @OneToMany(mappedBy = "parentVersion")
    @JsonIgnoreProperties({"parent"})
    private Collection<ResourceVersions> children;

    @JsonView(PublicView.class)
    @JsonDeserialize(using = UnitTimestampDeserializer.class)
    @Column(name = "created_at")
    //@DateTimeFormat(iso = DateTimeFormat.ISO.DATE_TIME)
    @DateTimeFormat(pattern = "yyyy-MM-dd'T'HH:mm:ss",
            iso = DateTimeFormat.ISO.DATE_TIME)
    private Date createdAt;

    @JsonView(InternalView.class)
    @Size(max = 4096, message = "Cannot be over 4096 characters long")
    @Column(name = "core_id")
    private String coreId;

    @JsonView(PublicView.class)
    @NotEmpty
    @Column(name = "version_purpose")
    private String versionPurpose = "create";

    @JsonView(InternalView.class)
    @Size(max = 50, message = "Cannot be over 50 characters long")
    @Column(name = "origin_reference")
    private String originReference;

    @JsonView(InternalView.class)
    @Size(max = 50, message = "Cannot be over 50 characters long")
    @Column(name = "origin_system")
    private String originSystem;

    @JsonView(InternalView.class)
    @Size(max = 40, message = "Cannot be over 40 characters long")
    @Column(name = "user_id")
    private String userId;

    @JsonView(InternalView.class)
    @Column(name = "linear_versioning")
    private boolean linearVersioning;

    @PrePersist
    public void prePersist() throws Exception {
        if (versionPurpose.compareToIgnoreCase("initial") == 0) {
            Date createdAt = Optional.ofNullable(this.createdAt).orElse(new Date());
            if (createdAt.toInstant().isAfter(Instant.now())) {
                throw new Exception("The created time is in the future");
            }
            this.createdAt = createdAt;
        } else {
            this.createdAt = new Date();
        }
    }

    public String getOriginSystem() {
        return originSystem;
    }

    public void setOriginSystem(String originSystem) {
        this.originSystem = originSystem;
    }

    public String getExternalReference() {
        return externalReference;
    }

    public void setExternalReference(String externalReference) {
        this.externalReference = externalReference;
    }

    public String getExternalUrl() {
        return externalUrl;
    }

    public void setExternalUrl(String externalUrl) {
        this.externalUrl = externalUrl;
    }

    public String getParent() {
        return parent;
    }

    @JsonSetter("parent")
    public void setParent(String parent) {
        this.parent = parent;
    }

    public String getExternalSystem() {
        return externalSystem;
    }

    public Date getCreatedAt() {
        return createdAt;
    }

    public void setCreatedAt(Date createdAt) {
        this.createdAt = createdAt;
    }

    public void setExternalSystem(String externalSystem) {
        this.externalSystem = externalSystem;
    }

    public String getId() {
        return this.id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public Collection<ResourceVersions> getChildren() {
        if (children == null) {
            children = Collections.EMPTY_LIST;
        }
        return children;
    }

    public void setChildren(Collection<ResourceVersions> children) {
        this.children = children;
    }

    public String getCoreId() {
        return coreId;
    }

    public void setCoreId(String coreId) {
        this.coreId = coreId;
    }

    public String getVersionPurpose() {
        return versionPurpose;
    }

    public void setVersionPurpose(String versionPurpose) {
        this.versionPurpose = versionPurpose;
    }

    public String getOriginReference() {
        return originReference;
    }

    public void setOriginReference(String originReference) {
        this.originReference = originReference;
    }

    public String getUserId() {
        return userId;
    }

    public void setUserId(String userId) {
        this.userId = userId;
    }

    @JsonGetter("parent")
    public ResourceVersions getParentVersion() {
        return parentVersion;
    }

    public void setParentVersion(ResourceVersions parentVersion) {
        this.parentVersion = parentVersion;
    }

    protected ResourceVersions() {
        //jpa only
    }

    public ResourceVersions(String externalSystem, String externalReference, String externalUrl) {
        this.externalSystem = externalSystem;
        this.externalReference = externalReference;
        this.externalUrl = externalUrl;
    }

    public ResourceVersions(String externalSystem, String externalReference, String externalUrl, String versionPurpose) {
        this(externalSystem, externalReference, externalUrl);
        this.versionPurpose = versionPurpose;
    }

    public ResourceVersions(String externalSystem, String externalReference, String externalUrl, String versionPurpose, String parent) {
        this(externalSystem, externalReference, externalUrl, versionPurpose);
        this.parent = parent;
    }


    public ResourceVersions(
            String externalSystem,
            String externalReference,
            String externalUrl,
            String versionPurpose,
            String originSystem,
            String originReference
    ) {
        this(externalSystem, externalReference, externalUrl, versionPurpose);
        this.originSystem = originSystem;
        this.originReference = originReference;
    }

    @Override
    public boolean equals(Object obj) {
        if (!(obj instanceof ResourceVersions)) {
            return false;
        }
        if (obj == this) {
            return true;
        }

        ResourceVersions resource = (ResourceVersions) obj;
        return new EqualsBuilder()
                .append(id, resource.id)
                .append(externalSystem, resource.externalSystem)
                .append(externalReference, resource.externalReference)
                .append(externalUrl, resource.externalUrl)
                .append(parent, resource.parent)
                .append(coreId, resource.coreId)
                .append(userId, resource.userId)
                .append(versionPurpose, resource.versionPurpose)
                .append(originSystem, resource.originSystem)
                .append(originReference, resource.originReference)
                .isEquals();
    }
}
