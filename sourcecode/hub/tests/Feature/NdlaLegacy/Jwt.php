<?php

declare(strict_types=1);

namespace Tests\Feature\NdlaLegacy;

final readonly class Jwt
{
    public static function sign(mixed $payload): string
    {
        return \Firebase\JWT\JWT::encode($payload, <<<KEY
        -----BEGIN PRIVATE KEY-----
        MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCwj9LzSfosb7p1
        dysYFAvJoCKkHArj7TP2Z89jCcYvco5WoCLpn8kyY2grrZM2G5CrIY1p8i9caf/G
        EclpPMhbYiaIjm6zhj6X176s7WlBQLxJYYFElhAqqefgpV/nuJOcV+Tl3nQc9UU+
        seAwAbLmYLE0KhH16P2XBtDHKkyPUNmq5sUvt0G2c4lmLxSVh/KmC7duCOCPqmPr
        sn8M//owW+5BSmsPX4vvh4cOjL1/apEaBpkADKdBoCp5stFydBxTBYAtzg1rL6Wb
        uy7HoghZoTDS13cSM3y8bYiuPGhhJmqdFB1zOuMum+8Myu4YysNQbGjCR+y0hRao
        8iQj8YYZAgMBAAECggEABPj1r/Rh+sCvXDiwGpV/Fljh6gQkvKjb0TXdhxI7+Fxn
        SB3YJwMX33/9NMbY9DwCgU9RvWRl3H62mMxOTGdNQc6+aVxr1klb4MiYaZHKsaYN
        p3Wx0buIMitm0huxpFYq/8sQw9GxLJsJdxBEiBWWcxsamVxah2y5ieYxhny0xGtT
        B8MdWDBv5UF7Y33nz2ie4mGuU0Y3nbX8CBUD1Xxy81U14qMuxps5wFOhZojJm55u
        P2JLL9PgphATUSX5fawOr7HAaVVzuocFOcaNXzaFGVxE8uQCR141TJEkmxF1Qcd4
        JaTGg8ZQBQ4C458mDe4N9JRlZSWHfxLCiQG48+fEoQKBgQDrfL6uvms5Hhnp9ppy
        xMQSHv9s7ZUROTQaemUsZEF+JnJQWJyZT4UMuGRynkerEJPkJZD8IzMyQTVYDXbp
        i71hSEUDkhsq5fyvAQupNGBXCwyW/kLP2iVWEPMHcatuOuApSETzIbh4ChxdYdW6
        fqqNxEeH6yKTSWihjbZ3qgpb5wKBgQC/8RGpuCS12pOMrOeNbDUiXrUCpEfRVEI2
        DhXvFSDccgis1I7fIivsahDtnpHF5MSBMO/Jlh+c1ibx9LGh92F6zI2/Y+GUDwq8
        KSQl02P3tCgYSFJPufgatqHP6NKOi4sZoLBP9+ngnCHlIvy8fN1pVUoDf9/pBxfL
        xECiBrjN/wKBgDMXdpudLtBOqpqraWkbLdsspIhyp5P8EETqZ0cRXXBMUSMDhr5w
        lVJkM0727l+8Ego+6Ez8KiBuK2+2RCV5rxYLQwX6TjCpg4BIvsnwVjKscAfWlJJa
        Jx2cQc5MqEAbQAVU67jAiTBqKStNjbyPvNtTSZ3As1i3ZZ8fiwY9e0/jAoGAMZ2y
        UCn+q1emHo4viPo4vfq3Vch7nBvoxapcdyICDZoYrfyANiPSMNm2KIZ97ibVuQOa
        aIS7BULCbHcxV8nn0+N+nMPH8wr0XnFQG8sKI2TZnHVNebh77wPYzjLEAv8FZQmJ
        qhJOzbXueRnTNTId1fWrHT539ffUaeMARiHwsacCgYAZuQLx3LartSc0Btm/qS/W
        D1B/v6HxfbOBx7Sf9XiRghVlTwhYBHrxSePoJICCT/XrAER/0o3EAjnOkcV+G3ws
        M36mGQMeJ+dPVhFSdwvmTMi9tiEr40q0ItK+mkLuM1OQRvQVsm9uxXx5RbmEombG
        CACkUXeKv+aAnK1Did7SkA==
        -----END PRIVATE KEY-----
        KEY, 'RS256');
    }
}
