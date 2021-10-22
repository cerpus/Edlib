package no.cerpus.versioning.exceptions;


public class ResourceNotFoundException extends RuntimeException {

    private String id;

    public ResourceNotFoundException(String id) {
        super(String.format("The resource '%s' was not found.", id));
        this.id = id;
    }

    public String getId() {
        return id;
    }
}