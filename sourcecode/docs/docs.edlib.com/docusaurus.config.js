/** @type {import('@docusaurus/types').DocusaurusConfig} */
module.exports = {
  title: 'Edlib',
  tagline: 'Edlib is the intelligent option to manage your interactive learning resources',
  url: 'https://docs.edlib.com',
  baseUrl: '/',
  onBrokenLinks: 'throw',
  onBrokenMarkdownLinks: 'warn',
  favicon: 'img/favicon.ico',
  organizationName: 'cerpus',
  projectName: 'Edlib',
  themeConfig: {
    navbar: {
      title: 'Edlib',
      logo: {
        alt: 'Edlib Logo',
        src: 'img/edlib-logo.png',
      },
      items: [
        {to: '/docs/intro', label: 'Documentation', position: 'left'},
        {to: '/docs/developers/getting-started', label: 'Developers', position: 'left'},
        {to: '/docs/support/faq', label: 'Support', position: 'left'},
        {to: '/blog', label: 'Blog', position: 'left'},
        {to: '/careers', label: 'Careers', position: 'left'},
        {to: '/contact-us', label: 'Contact Us', position: 'left'},
        {
          href: 'https://github.com/cerpus/Edlib',
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
              label: 'Ecosystem',
              to: '/docs/product/ecosystem',
            },            
            {
              label: 'Demos',
              to: '/docs/product/demos',
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
              label: 'Contributing',
              to: '/docs/developers/contributing',
            },
            {
              label: 'API documentation',
              to: '/docs/developers/api-documentation/introduction',
            },
            {
              label: 'Plugins',
              to: '/docs/developers/plugins',
            },
          ],
        },
        {
          title: 'Support',
          items: [
            {
              label: 'FAQ',
              to: '/docs/support/faq',
            },
            {
              label: 'User guides',
              to: '/docs/support/userGuides/create-content',
            },
            {
              label: 'Contact support',
              to: '/docs/support/contacting-support',
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
              label: 'Careers',
              to: '/careers',
            },
            {
              label: 'Contact us',
              to: '/contact-us',
            },
            {
              label: 'GitHub',
              href: 'https://github.com/cerpus/Edlib',
            },
          ],
        },
      ],
      copyright: `Copyright &copy; ${new Date().getFullYear()} Edlib &mdash; <a href="https://cerpus.com">Cerpus</a>`,
    },
  },
  presets: [
    [
      '@docusaurus/preset-classic',
      {
        docs: {
          sidebarPath: require.resolve('./sidebars.js'),
        },
        blog: {
          showReadingTime: true,
        },
        theme: {
          customCss: require.resolve('./src/css/custom.css'),
        },
      },
    ],
  ],
};
