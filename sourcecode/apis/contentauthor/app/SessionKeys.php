<?php

namespace App;

/**
 * A central place to name session keys, use if one key is used in more than one source file.
 */
class SessionKeys
{
    /*
     * Question Set data from a LTI request
     */
    public const EXT_QUESTION_SET = 'ext_question_set';
    public const EXT_BEHAVIOR_SETTINGS = 'ext_behavior_settings';
    public const EXT_EDITOR_BEHAVIOR_SETTINGS = 'ext_editor_behavior_settings.%s';
    public const EXT_CSS_URL = 'launch_presentation_css_url';
}
