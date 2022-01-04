package no.cerpus.versioning.web;

import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RestController;

import java.util.HashMap;
import java.util.Map;

@RestController
public class HealthController {
    @GetMapping("/healthy")
    public Map healthy() {
        Map<String,String> map = new HashMap<>();
        map.put("healthy", "yes");
        return map;
    }
}
