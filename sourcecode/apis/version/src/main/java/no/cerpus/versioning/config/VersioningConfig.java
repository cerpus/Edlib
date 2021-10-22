package no.cerpus.versioning.config;

import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.ComponentScan;
import org.springframework.context.annotation.Configuration;
import org.springframework.context.annotation.EnableAspectJAutoProxy;
import org.springframework.validation.beanvalidation.CustomValidatorBean;

import javax.validation.Validator;

@Configuration
@EnableAspectJAutoProxy
@ComponentScan
public class VersioningConfig {
}
