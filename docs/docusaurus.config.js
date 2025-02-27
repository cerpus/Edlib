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
  plugins: [
    'docusaurus-plugin-matomo',
  ],
  themeConfig: {
    announcementBar: {
      id: 'cookie_consent',
      content:
        'This website uses cookies to ensure you get the best experience on our website. <a target="_blank" rel="noopener noreferrer" href="https://www.cookiesandyou.com/"> Learn more</a>',
      backgroundColor: '#72aa8f',
      textColor: '#ffffff',
      isCloseable: true,
    },
    matomo: {
      // Matomo is for web analytics
      matomoUrl: 'https://matomo.cerpus.com/',
      siteId: '3',
      phpLoader: 'matomo.php',
      jsLoader: 'matomo.js',
    },
    algolia: {
      // The application ID provided by Algolia
      appId: 'LP932E7C3J',

      // Public API key: it is safe to commit it
      apiKey: 'b9e7b7df4d07385dedfe816e1f554cc0',

      indexName: 'edlib',

      // Optional: see doc section below
      contextualSearch: true,

      // Optional: Specify domains where the navigation should occur through window.location instead on history.push. Useful when our Algolia config crawls multiple documentation sites and we want to navigate with window.location.href to them.
      externalUrlRegex: 'external\\.com|domain\\.com',

      // Optional: Algolia search parameters
      searchParameters: {},

      // Optional: path for search page that enabled by default (`false` to disable it)
      searchPagePath: 'search',

      //... other Algolia params
      placeholder: 'Search Edlib docs'
    },
    navbar: {
      //title: 'Edlib',
      hideOnScroll: true,
      logo: {
        alt: 'Edlib Logo',
        src: 'img/edlib-logo.png',
        srcDark: 'img/edlib-logo_dark.png',
      },
      items: [
        {to: '/docs/intro', label: 'Documentation', position: 'left'},
        {to: '/docs/developers/getting-started', label: 'Developers', position: 'left'},
        {to: '/docs/support/faq', label: 'Support', position: 'left'},
        {to: '/blog', label: 'Blog', position: 'left'},
        {to: '/contact-us', label: 'Contact Us', position: 'left'},
        {to: '/pricing', label: 'Pricing', position: 'left'},
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
      copyright: `Copyright &copy; ${new Date().getFullYear()} Edlib &mdash; <a href="https://cerpus.com">Edlib AS</a>`,
    },
    prism: {
      additionalLanguages: [
        'php',
      ],
    },
  },
  presets: [
    [
      '@docusaurus/preset-classic',
      {
        googleTagManager: {
          containerId: 'GTM-KNJNCK6D',
        },
        docs: {
          sidebarPath: require.resolve('./sidebars.js'),
        },
        blog: {
          showReadingTime: true,
        },
        theme: {
          customCss: [require.resolve('./src/css/custom.css')],
        },
      },
    ],
  ],
};
