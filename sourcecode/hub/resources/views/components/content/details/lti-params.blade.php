<details>
    <summary class="fs-5">LTI params</summary>

    <dl>
        <dt>ID</dt>
        <dd><kbd>{{ $version->content->id }}</kbd></dd>

        <dt>Version ID</Dt>
        <dd><kbd>{{ $version->id }}</kbd></dd>

        <dt>Tool ID</dt>
        <dd><kbd>{{ $version->lti_tool_id }}</kbd></dd>

        <dt>Presentation launch URL</dt>
        <dd><kbd>{{ $version->lti_launch_url }}</kbd></dd>
    </dl>

    <x-lti-debug
        :url="$launch->getRequest()->getUrl()"
        :parameters="$launch->getRequest()->toArray()"
    />
</details>
