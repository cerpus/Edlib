<?php

declare(strict_types=1);

namespace App\Models;

enum LtiToolEditMode: string
{
    /**
     * Launch the tool URL, and create a new version with the result.
     */
    case Replace = 'replace';

    /**
     * Launch the content URL as a deep-linking tool, and create a new version
     * with the result.
     */
    case DeepLinkingRequestToContentUrl = 'deep_linking_request_to_content_url';
}
