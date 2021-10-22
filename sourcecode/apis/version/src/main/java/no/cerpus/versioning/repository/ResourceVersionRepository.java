package no.cerpus.versioning.repository;

import no.cerpus.versioning.models.ResourceVersions;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.Optional;


@Repository
public interface ResourceVersionRepository extends JpaRepository<ResourceVersions, String> {

    @Query("SELECT obj FROM ResourceVersions obj LEFT JOIN FETCH obj.children WHERE obj.id = :id")
    Optional<ResourceVersions> findByIdWithChildren(String id);


    ResourceVersions findByExternalSystemAndExternalReference(String externalSystem, String externalReference);

    List<ResourceVersions> findByParent(String parentId);

    ResourceVersions findByOriginSystemAndOriginReference(String originSystem, String originReference);
}
