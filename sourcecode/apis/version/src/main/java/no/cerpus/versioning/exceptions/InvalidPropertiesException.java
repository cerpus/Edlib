package no.cerpus.versioning.exceptions;

import org.springframework.validation.BindingResult;

public class InvalidPropertiesException extends RuntimeException {

    private BindingResult errors;

    public InvalidPropertiesException(BindingResult errors) {
        this.errors = errors;
    }

    public BindingResult getErrors() {
        return errors;
    }
}
