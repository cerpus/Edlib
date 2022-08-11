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
    const EXT_QUESTION_SET = 'ext_question_set';
    const EXT_BEHAVIOR_SETTINGS = 'ext_behavior_settings';
    const EXT_EDITOR_BEHAVIOR_SETTINGS = 'ext_editor_behavior_settings.%s';
    const EXT_CSS_URL = 'launch_presentation_css_url';
}
