/** @type {import('@docusaurus/types').DocusaurusConfig} */
module.exports = {
  title: 'Edlib',
  tagline: 'Edlib is the intelligent option to manage your interactive learning resources in the cloud',
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
        {to: '/docs/product/faq', label: 'FAQs', position: 'left'},
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
              label: 'User guides',
              to: '/docs/product/user-guides',
            },
            {
              label: 'Demos',
              to: '/docs/product/demos',
            },
            {
              label: 'Frequently asked questions',
              to: '/docs/product/faq',
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
              to: '/docs/developers/api-documentation/introduction',
            },
            {
              label: 'Plugins',
              to: '/docs/developers/plugins',
            },
          ],
        },
        {
          title: 'Community',
          items: [
            {
              label: 'Contributing',
              to: '/docs/community/contributing',
            },
            {
              label: 'Support',
              to: '/docs/community/support',
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
