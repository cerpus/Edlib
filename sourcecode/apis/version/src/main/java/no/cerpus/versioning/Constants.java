package no.cerpus.versioning;


public class Constants {

    private Constants() {
    }

    public static final String API_VERSION = "/v1";

    public static final String API_PREFIX = "/resources";

    public static final String API_CORE = "/core/{externalSystem}/{externalReference}";

    public static final String API_LATEST = "/{id}/latest";

    public static final String API_EXTERNAL = "/{externalSystem}/{externalId}";

}
