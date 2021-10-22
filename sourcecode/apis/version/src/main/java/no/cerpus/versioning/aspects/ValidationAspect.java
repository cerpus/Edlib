package no.cerpus.versioning.aspects;

import no.cerpus.versioning.exceptions.InvalidPropertiesException;
import org.aspectj.lang.JoinPoint;
import org.aspectj.lang.annotation.Aspect;
import org.aspectj.lang.annotation.Before;
import org.springframework.stereotype.Component;
import org.springframework.validation.BindingResult;

@Aspect
@Component
public class ValidationAspect {

    @Before("execution(public * * (.., @org.springframework.validation.annotation.Validated (*), org.springframework.validation.BindingResult ,..))")
    public void handleValidationErrors(JoinPoint joinPoint) {
        BindingResult result = null;
        for (Object arg : joinPoint.getArgs()) {
            if (arg instanceof BindingResult) {
                result = (BindingResult) arg;
                break;
            }
        }
        if (result == null) {
            return;
        }
        if (result.hasErrors()) {
            throw new InvalidPropertiesException(result);
        }
    }
}
