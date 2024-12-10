import SwaggerUI from 'swagger-ui';
import 'swagger-ui/dist/swagger-ui.css';

SwaggerUI({
    dom_id: '#swagger',
    url: document.documentElement.getAttribute('data-openapi-url'),
});
