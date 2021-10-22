package no.cerpus.versioning.response;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import no.cerpus.versioning.exceptions.InvalidPropertiesException;
import no.cerpus.versioning.exceptions.ResourceNotFoundException;
import no.cerpus.versioning.exceptions.ServiceUnavailableException;
import no.cerpus.versioning.models.ResourceVersions;
import no.cerpus.versioning.services.ResourceVersioningService;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.validation.BindingResult;
import org.springframework.validation.FieldError;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.context.request.WebRequest;
import org.springframework.web.servlet.mvc.method.annotation.ResponseEntityExceptionHandler;

import java.util.Collection;
import java.util.List;

@RestControllerAdvice(annotations = RestController.class)
public class RestExceptionHandler extends ResponseEntityExceptionHandler {
    private ResourceVersioningService resourceVersioningService;

    public RestExceptionHandler(ResourceVersioningService resourceVersioningService) {
        this.resourceVersioningService = resourceVersioningService;
    }

    @ExceptionHandler(Exception.class)
    public ResponseEntity handleExceptions(Exception ex, WebRequest request) {
        ResponseMessage responseMessage = initFailureResponse("Something wrong happened");
        responseMessage.addError(ex.getMessage());
        return ResponseEntity.badRequest().body(responseMessage);
    }

    @ExceptionHandler(ResourceNotFoundException.class)
    public ResponseEntity handleResourceNotFoundException(ResourceNotFoundException e, WebRequest request) {
        ResponseMessage responseMessage = initFailureResponse("The request failed");
        responseMessage.addError(e.getMessage());
        return ResponseEntity.status(HttpStatus.NOT_FOUND).body(responseMessage);
    }

    private ResponseMessage initFailureResponse(String failureMessage) {
        return ResponseMessage.failure(failureMessage);
    }

    @ExceptionHandler(InvalidPropertiesException.class)
    public ResponseEntity handleInvalidPropertiesException(InvalidPropertiesException e) {
        ResponseMessage responseMessage = initFailureResponse("The request had invalid properties.");

        BindingResult errors = e.getErrors();
        List<FieldError> fieldErrors = errors.getFieldErrors();

        if (!fieldErrors.isEmpty()) {
            fieldErrors.stream().forEach((error) -> {
                String message = String.format("%s, current value is '%s'", error.getDefaultMessage(), error.getRejectedValue());
                responseMessage.addError(error.getField(), error.getCode(), message);
            });
        }

        return ResponseEntity.badRequest().body(responseMessage);
    }

    @ExceptionHandler(ServiceUnavailableException.class)
    @ResponseStatus(HttpStatus.SERVICE_UNAVAILABLE)
    public ResponseMessage handleServiceUnavailableException() {
        ResponseMessage responseMessage = initFailureResponse("The request failed");
        responseMessage.addError("Service unavailable");
        return responseMessage;
    }
    @ExceptionHandler(ResourceVersioningService.ResourceVersioningLinearVersioningException.class)
    @ResponseStatus(HttpStatus.CONFLICT)
    public LinearVersioningConflictError linearVersioningConflictError(ResourceVersioningService.ResourceVersioningLinearVersioningException e) {
        return new LinearVersioningConflictError(e, resourceVersioningService.getLeafs(e.getRequestedParent().getId()));
    }

    public static class LinearVersioningConflictError {
        private ResourceVersions requestedParent;
        private Collection<ResourceVersions> leafs;
        private String error;

        public LinearVersioningConflictError(ResourceVersions requestedParent, Collection<ResourceVersions> leafs) {
            this.requestedParent = requestedParent;
            this.leafs = leafs;
            this.error = "Linear versioning constraint violation";
        }
        public LinearVersioningConflictError(ResourceVersioningService.ResourceVersioningLinearVersioningException e, Collection<ResourceVersions> leafs) {
            this(e.getRequestedParent(), leafs);
        }

        @JsonIgnoreProperties({"parent", "children"})
        public ResourceVersions getRequestedParent() {
            return requestedParent;
        }

        @JsonIgnoreProperties("parent")
        public Collection<ResourceVersions> getLeafs() {
            return leafs;
        }

        public String getError() {
            return error;
        }
    }
}
