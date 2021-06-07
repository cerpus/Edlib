import React from 'react';
import clsx from 'clsx';
import styles from './HomepageFeatures.module.css';

const FeatureList = [
  {
    title: 'Easy to Use',
    Svg: require('../../static/img/options.svg').default,
    description: (
      <>
        Edlib was designed from the ground up to be as intuitive as possible while still providing you access to advanced collaboration, licensing and sharing features. Edlib is also easily integrated into third-party learning applications.
      </>
    ),
  },
  {
    title: 'Focus on What Matters',
    Svg: require('../../static/img/focus.svg').default,
    description: (
      <>
        Edlib lets you focus on your content with efficient workflows for creating and managing content, licensing content appropriately, collaborating with other content creators, sharing content with learners and ultimately, getting an understanding of how students are interacting with the learning content.
      </>
    ),
  },
  {
    title: 'Powered by H5P',
    Svg: require('../../static/img/powerful.svg').default,
    description: (
      <>
         Edlib was specificlly developed to enable the straightforward creation and management of <a href="https://h5p.org/">H5P</a>-based interactive learning resources.
      </>
    ),
  },
];

function Feature({Svg, title, description}) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center">
        <Svg className={styles.featureSvg} alt={title} />
      </div>
      <div className="text--center padding-horiz--md">
        <h3>{title}</h3>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures() {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}
