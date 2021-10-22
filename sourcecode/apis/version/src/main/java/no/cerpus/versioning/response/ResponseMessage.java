package no.cerpus.versioning.response;


import com.fasterxml.jackson.annotation.JsonView;
import no.cerpus.versioning.models.ResourceVersions;
import no.cerpus.versioning.views.Views;

import java.util.ArrayList;
import java.util.List;

public class ResponseMessage<T> {

    @JsonView(Views.Public.class)
    private T data;

    @JsonView(Views.Public.class)
    private List<Error> errors = new ArrayList<Error>();

    @JsonView(Views.Public.class)
    private Type type;
    @JsonView(Views.Public.class)
    private String message;

    public enum Type {
        success, failure;
    }

    public ResponseMessage(Type type, String message) {
        this.type = type;
        this.message = message;
    }

    public ResponseMessage(Type type, T data) {
        this.type = type;
        this.data = data;
    }

    public static ResponseMessage failure(String message) {
        return new ResponseMessage(Type.failure, message);
    }

    public static <T> ResponseMessage<T> success(T responseData) {
        return new ResponseMessage<T>(Type.success, responseData);
    }

    public Type getType() {
        return type;
    }

    public void setType(Type type) {
        this.type = type;
    }

    public String getMessage() {
        return message;
    }

    public void setMessage(String message) {
        this.message = message;
    }

    public T getData() {
        return data;
    }

    public void setData(T data) {
        this.data = data;
    }

    public List<Error> getErrors() {
        return errors;
    }

    public void setErrors(List<Error> errors) {
        this.errors = errors;
    }

    public void addError(String field, String code, String message) {
        this.errors.add(new Error(field, code, message));
    }

    public void addError(String message) {
        this.errors.add(new Error(message));
    }

    class Error {

        private String code;
        private String message;
        private String field;

        private Error(String field, String code, String message) {
            this.field = field;
            this.code = code;
            this.message = message;
        }

        private Error(String message) {
            this.message = message;
        }

        public String getCode() {
            return code;
        }

        public void setCode(String code) {
            this.code = code;
        }

        public String getMessage() {
            return message;
        }

        public void setMessage(String message) {
            this.message = message;
        }

        public String getField() {
            return field;
        }

        public void setField(String field) {
            this.field = field;
        }
    }
}
