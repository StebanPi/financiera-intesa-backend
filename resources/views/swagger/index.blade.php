<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    
    <!-- Swagger UI CSS -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.17.14/swagger-ui.css" />
    
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *,
        *:before,
        *:after {
            box-sizing: inherit;
        }
        body {
            margin: 0;
            background: #fafafa;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>

    <!-- Swagger UI JavaScript -->
    <script src="https://unpkg.com/swagger-ui-dist@5.17.14/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.17.14/swagger-ui-standalone-preset.js"></script>

    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "/docs/openapi.json",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                docExpansion: "list",
                filter: true,
                persistAuthorization: true,
                tryItOutEnabled: true,
                requestInterceptor: function(request) {
                    // Asegurar que las requests tienen Accept: application/json para evitar redirects
                    if (!request.headers['Accept']) {
                        request.headers['Accept'] = 'application/json';
                    }
                    return request;
                },
                onComplete: function() {
                    // El botón "Authorize" estará disponible en la UI
                    // Los usuarios pueden hacer clic y agregar su Bearer Token manualmente
                }
            });

            window.ui = ui;
        };
    </script>
</body>
</html>
