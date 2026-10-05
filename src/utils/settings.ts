import { SiteSettings, AnnouncementItem } from '../types';
import { DEFAULT_SITE_SETTINGS } from '../data/defaultSettings';

/**
 * Retrieves a setting value from the SiteSettings object with fallback to default
 */
export function getSetting<K extends keyof SiteSettings>(
  settings: SiteSettings | null | undefined,
  key: K,
  fallback?: SiteSettings[K]
): SiteSettings[K] {
  if (settings && settings[key] !== undefined && settings[key] !== null) {
    return settings[key];
  }
  if (fallback !== undefined) {
    return fallback;
  }
  return DEFAULT_SITE_SETTINGS[key];
}

/**
 * Formats a Pakistani phone number for WhatsApp links (stripping leading 0 and adding +92)
 */
export function formatWhatsappUrl(number: string, message?: string): string {
  const clean = number.replace(/\D/g, '');
  const normalized = clean.startsWith('92') ? clean : clean.startsWith('0') ? `92${clean.slice(1)}` : `92${clean}`;
  const base = `https://wa.me/${normalized}`;
  return message ? `${base}?text=${encodeURIComponent(message)}` : base;
}

/**
 * Filter announcements by location and date
 */
export function getActiveAnnouncements(
  announcements: AnnouncementItem[],
  location: 'top_bar' | 'homepage_banner' | 'popup'
): AnnouncementItem[] {
  const now = new Date();
  return announcements.filter((item) => {
    if (!item.isActive) return false;
    if (item.displayLocation !== location) return false;
    if (item.startDate) {
      const start = new Date(item.startDate);
      if (now < start) return false;
    }
    if (item.expiryDate) {
      const expiry = new Date(item.expiryDate);
      if (now > expiry) return false;
    }
    return true;
  });
}
