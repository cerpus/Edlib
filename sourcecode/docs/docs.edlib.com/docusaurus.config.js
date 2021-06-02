/** @type {import('@docusaurus/types').DocusaurusConfig} */
module.exports = {
  title: 'Edlib',
  tagline: 'Edlib allows you to create, store, share and use rich, interactive learning resources in the cloud',
  url: 'https://docs.edlib.com',
  baseUrl: '/',
  onBrokenLinks: 'throw',
  onBrokenMarkdownLinks: 'warn',
  favicon: 'img/favicon.ico',
  organizationName: 'cerpus', // Usually your GitHub org/user name.
  projectName: 'Edlib', // Usually your repo name.
  themeConfig: {
    navbar: {
      title: 'Edlib',
      logo: {
        alt: 'Edlib Logo',
        src: 'img/edlib-logo.png',
      },
      items: [
        {to: '/blog', label: 'Blog', position: 'left'},
        {
          href: 'https://github.com/cerpus',
          label: 'GitHub',
          position: 'right',
        },
      ],
    },
    footer: {
      style: 'dark',
      links: [
        {
          title: 'Product',
          items: [
            {
              label: 'Features',
              to: '/docs/product/features',
            },
            {
              label: 'Roadmap',
              to: '/docs/product/roadmap',
            },
            {
              label: 'User guides',
              to: '/docs/product/user-guides',
            },
            {
              label: 'Demos',
              to: '/docs/product/demos',
            },
            {
              label: 'Knowledge base',
              to: '/docs/product/knowledge-base',
            },
            {
              label: 'Launch & success support',
              to: '/docs/product/launch-success-support',
            },
          ],
        },
        {
          title: 'Solutions',
          items: [
            {
              label: 'Case studies',
              to: '/docs/solutions/case-studies',
            },
            {
              label: 'Open source',
              to: '/docs/solutions/open-source',
            },
            {
              label: 'Custom development',
              to: '/docs/solutions/custom-development',
            },
          ],
        },
        {
          title: 'Developers',
          items: [
            {
              label: 'Getting started',
              to: '/docs/developers/getting-started',
            },
            {
              label: 'Architecture',
              to: '/docs/developers/architecture',
            },
            {
              label: 'In-production usage guide',
              to: '/docs/developers/in-production-usage-guide',
            },
            {
              label: 'API documentation',
              to: '/docs/developers/api-documentation',
            },
            {
              label: 'Plugins',
              to: '/docs/developers/plugins',
            },
          ],
        },
        {
          title: 'About',
          items: [
            {
              label: 'Blog',
              to: '/blog',
            },
            {
              label: 'Contact us',
              to: '/contact-us',
            },
            {
              label: 'Careers',
              to: '/careers',
            },
            {
              label: 'GitHub',
              href: 'https://github.com/cerpus',
            },
          ],
        },
      ],
      copyright: `Copyright © ${new Date().getFullYear()} Edlib &mdash; <a href="https://cerpus.com">Cerpus</a>`,
    },
  },
  presets: [
    [
      '@docusaurus/preset-classic',
      {
        docs: {
          sidebarPath: require.resolve('./sidebars.js'),
          // Please change this to your repo.
          editUrl:
            'https://github.com/facebook/docusaurus/edit/master/website/',
        },
        blog: {
          showReadingTime: true,
          // Please change this to your repo.
          editUrl:
            'https://github.com/facebook/docusaurus/edit/master/website/blog/',
        },
        theme: {
          customCss: require.resolve('./src/css/custom.css'),
        },
      },
    ],
  ],
};
