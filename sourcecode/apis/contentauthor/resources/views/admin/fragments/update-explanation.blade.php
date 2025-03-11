Installing or updating content types may also install or update other content types and libraries.
<br>
For <dfn title="The first part of the version number, e.g. the 3 in 3.6.42">major</dfn> and
<dfn title="The middle part of the verison number, e.g. the 6 in 3.6.42">minor</dfn>
version updates, the new version will be installed in addition to existing versions.
For <dfn title="The last part of the version number, e.g. the 42 in 3.6.42">patch</dfn>
version updates, the new version will replace installed version with same major and minor version and lower patch version.
E.g. new version <code>3.6.42</code> will replace <code>3.6.23</code>, but not <code>3.6.47</code>, <code>3.5.21</code>, <code>2.6.29</code> or <code>3.7.18</code>.
