import { Listing, JobPost } from '../types';

interface SEOConfig {
  title: string;
  description: string;
  image?: string;
  url?: string;
  type?: 'website' | 'product' | 'article' | 'profile';
  jsonLd?: Record<string, any>;
}

const DEFAULT_SEO: SEOConfig = {
  title: 'SargodhaMart - Buy • Sell • Jobs • Grow (Sargodha | Shaheenabad | Sillanwali)',
  description:
    'Premier local marketplace and employment platform for Sargodha, Shaheenabad, and Sillanwali. Direct call & WhatsApp trading, verified Rs. 1,000 lifetime seller activation, zero commission.',
  image: 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1200&q=80',
  type: 'website',
  jsonLd: {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    name: 'SargodhaMart',
    url: typeof window !== 'undefined' ? window.location.origin : 'https://sargodhamart.com',
    description: 'Premier local marketplace & employment platform for Sargodha, Shaheenabad, and Sillanwali.',
    potentialAction: {
      '@type': 'SearchAction',
      target: `${typeof window !== 'undefined' ? window.location.origin : ''}/?q={search_term_string}`,
      'query-input': 'required name=search_term_string',
    },
  },
};

export function updatePageSEO(config?: Partial<SEOConfig>) {
  if (typeof document === 'undefined') return;

  const currentOrigin = window.location.origin;
  const currentPath = window.location.pathname + window.location.search;
  const fullUrl = config?.url || `${currentOrigin}${currentPath}`;

  const title = config?.title || DEFAULT_SEO.title;
  const description = config?.description || DEFAULT_SEO.description;
  const image = config?.image || DEFAULT_SEO.image;
  const type = config?.type || DEFAULT_SEO.type;
  const jsonLd = config?.jsonLd || DEFAULT_SEO.jsonLd;

  // 1. Update document.title
  document.title = title;

  // Helper to update or create meta tags
  const setMeta = (name: string, content: string, isProperty = false) => {
    const attr = isProperty ? 'property' : 'name';
    let element = document.querySelector(`meta[${attr}="${name}"]`) as HTMLMetaElement | null;
    if (!element) {
      element = document.createElement('meta');
      element.setAttribute(attr, name);
      document.head.appendChild(element);
    }
    element.content = content;
  };

  // 2. Standard Meta Tags
  setMeta('description', description);

  // 3. OpenGraph Tags (Facebook, WhatsApp, LinkedIn, Slack)
  setMeta('og:title', title, true);
  setMeta('og:description', description, true);
  setMeta('og:url', fullUrl, true);
  setMeta('og:type', type || 'website', true);
  setMeta('og:site_name', 'SargodhaMart', true);
  if (image) {
    setMeta('og:image', image, true);
    setMeta('og:image:alt', title, true);
  }

  // 4. Twitter / X Cards
  setMeta('twitter:card', 'summary_large_image');
  setMeta('twitter:title', title);
  setMeta('twitter:description', description);
  if (image) {
    setMeta('twitter:image', image);
  }

  // 5. Canonical Link
  let canonical = document.querySelector('link[rel="canonical"]') as HTMLLinkElement | null;
  if (!canonical) {
    canonical = document.createElement('link');
    canonical.setAttribute('rel', 'canonical');
    document.head.appendChild(canonical);
  }
  canonical.href = fullUrl;

  // 6. Schema.org JSON-LD Structured Data
  let scriptTag = document.getElementById('sargodha-schema-ld') as HTMLScriptElement | null;
  if (!scriptTag) {
    scriptTag = document.createElement('script');
    scriptTag.id = 'sargodha-schema-ld';
    scriptTag.type = 'application/ld+json';
    document.head.appendChild(scriptTag);
  }
  scriptTag.textContent = JSON.stringify(jsonLd);
}

// Generate Product-Specific SEO Configuration
export function getProductSEO(listing: Listing, origin: string, siteName: string = 'SargodhaMart'): SEOConfig {
  const priceFormatted = `Rs. ${listing.price.toLocaleString()}`;
  const title = `${listing.title} – ${priceFormatted} | ${siteName}`;
  const description = `${listing.title} available for ${priceFormatted} in ${listing.city} (${listing.area}). Contact seller ${listing.sellerName} directly via Call or WhatsApp on ${siteName}.`;
  const image = listing.images[0] || DEFAULT_SEO.image;
  const url = `${origin}/?product=${listing.id}`;

  const jsonLd = {
    '@context': 'https://schema.org',
    '@type': 'Product',
    name: listing.title,
    image: listing.images,
    description: listing.description,
    category: listing.categoryName,
    itemCondition: listing.condition === 'New' ? 'https://schema.org/NewCondition' : 'https://schema.org/UsedCondition',
    offers: {
      '@type': 'Offer',
      price: listing.price,
      priceCurrency: 'PKR',
      availability: 'https://schema.org/InStock',
      url: url,
      seller: {
        '@type': 'Person',
        name: listing.sellerName,
        telephone: listing.sellerPhone,
        address: {
          '@type': 'PostalAddress',
          addressLocality: listing.city,
          addressRegion: 'Punjab',
          addressCountry: 'PK',
        },
      },
    },
  };

  return {
    title,
    description,
    image,
    url,
    type: 'product',
    jsonLd,
  };
}

// Generate Job-Specific SEO Configuration
export function getJobSEO(job: JobPost, origin: string, siteName: string = 'SargodhaMart'): SEOConfig {
  const typeLabel = job.postType === 'need_worker' ? 'Hiring' : 'Work Wanted';
  const salaryText = job.salaryOrPayment ? ` - ${job.salaryOrPayment}` : '';
  const title = `${job.title} – ${job.city} | ${siteName} Jobs`;
  const description = `${job.title} in ${job.city} (${job.area}). Required skills: ${job.skills}. Connect with ${job.posterName} directly on WhatsApp or phone on ${siteName}.`;
  const url = `${origin}/?job=${job.id}`;

  const jsonLd = {
    '@context': 'https://schema.org',
    '@type': 'JobPosting',
    title: job.title,
    description: job.description,
    datePosted: '2026-10-01',
    employmentType: 'FULL_TIME',
    hiringOrganization: {
      '@type': 'Organization',
      name: job.posterName,
      sameAs: origin,
    },
    jobLocation: {
      '@type': 'Place',
      address: {
        '@type': 'PostalAddress',
        streetAddress: job.area,
        addressLocality: job.city,
        addressRegion: 'Punjab',
        addressCountry: 'PK',
      },
    },
    baseSalary: job.salaryOrPayment
      ? {
          '@type': 'MonetaryAmount',
          currency: 'PKR',
          value: {
            '@type': 'QuantitativeValue',
            unitText: 'MONTH',
            value: job.salaryOrPayment,
          },
        }
      : undefined,
  };

  return {
    title,
    description,
    image: 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=1200&q=80',
    url,
    type: 'article',
    jsonLd,
  };
}

// Reset to Default Marketplace SEO
export function resetDefaultSEO() {
  updatePageSEO(DEFAULT_SEO);
}
