import React from 'react';
import { Phone, MessageCircle, ExternalLink, Sparkles, Megaphone, MapPin } from 'lucide-react';
import { Advertisement } from '../types';

interface SponsoredBannerProps {
  ad: Advertisement;
  variant?: 'banner' | 'card' | 'compact' | 'hero';
  onAdClick?: (ad: Advertisement) => void;
}

export const SponsoredBanner: React.FC<SponsoredBannerProps> = ({
  ad,
  variant = 'banner',
  onAdClick,
}) => {
  const cleanPhone = ad.phone.replace(/[^0-9+]/g, '');
  const cleanWhatsapp = ad.whatsapp.replace(/[^0-9+]/g, '');
  const whatsappUrl = `https://wa.me/${cleanWhatsapp}?text=${encodeURIComponent(
    `Assalam-o-Alaikum! I saw your sponsored ad "${ad.title}" on SargodhaMart.`
  )}`;

  if (variant === 'compact') {
    return (
      <div className="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-cyan-500/10 border border-amber-300/80 rounded-2xl p-3 shadow-xs relative overflow-hidden flex items-center justify-between gap-3">
        <div className="flex items-center gap-3 min-w-0">
          <div className="relative shrink-0">
            <img
              src={ad.imageUrl}
              alt={ad.title}
              className="w-12 h-12 rounded-xl object-cover border border-amber-300"
            />
            <span className="absolute -top-1 -right-1 px-1 py-0.2 rounded bg-amber-500 text-slate-950 font-black text-[9px] uppercase tracking-wider shadow-xs">
              Ad
            </span>
          </div>
          <div className="min-w-0">
            <div className="flex items-center gap-1.5">
              <span className="px-1.5 py-0.2 rounded-sm bg-amber-100 text-amber-900 border border-amber-300 text-[9px] font-black uppercase tracking-wider">
                Sponsored
              </span>
              <span className="text-[10px] text-slate-500 font-medium truncate flex items-center gap-0.5">
                <MapPin className="w-2.5 h-2.5 text-cyan-600" /> {ad.location}
              </span>
            </div>
            <h4 className="text-xs font-bold text-slate-900 truncate mt-0.5">{ad.title}</h4>
            <p className="text-[11px] text-slate-600 truncate">{ad.description}</p>
          </div>
        </div>

        <div className="flex items-center gap-1.5 shrink-0">
          <a
            href={`tel:${cleanPhone}`}
            className="p-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white shadow-xs transition-transform hover:scale-105"
            title="Call Business"
          >
            <Phone className="w-3.5 h-3.5" />
          </a>
          <a
            href={whatsappUrl}
            target="_blank"
            rel="noreferrer"
            className="p-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs transition-transform hover:scale-105 flex items-center gap-1"
            title="WhatsApp Business"
          >
            <MessageCircle className="w-3.5 h-3.5" />
          </a>
        </div>
      </div>
    );
  }

  if (variant === 'card') {
    return (
      <div className="bg-white rounded-3xl border-2 border-amber-300/80 shadow-[0_4px_20px_rgba(245,158,11,0.12)] overflow-hidden flex flex-col hover:border-amber-400 transition-all hover:shadow-lg">
        <div className="relative h-44 overflow-hidden bg-slate-900">
          <img
            src={ad.imageUrl}
            alt={ad.title}
            className="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
          />
          <div className="absolute top-3 left-3 flex items-center gap-1.5">
            <span className="px-2.5 py-1 rounded-full bg-amber-400 text-slate-950 font-black text-[10px] uppercase tracking-wider shadow-md flex items-center gap-1">
              <Sparkles className="w-3 h-3 fill-slate-950" /> Sponsored
            </span>
            <span className="px-2 py-0.5 rounded-full bg-slate-900/80 backdrop-blur-md text-cyan-300 text-[10px] font-bold border border-cyan-400/40">
              {ad.packageName}
            </span>
          </div>
          <div className="absolute bottom-2 left-3 right-3 text-white text-[11px] font-semibold bg-slate-950/60 backdrop-blur-xs px-2.5 py-1 rounded-xl flex items-center gap-1">
            <MapPin className="w-3 h-3 text-cyan-400 shrink-0" />
            <span className="truncate">{ad.location}</span>
          </div>
        </div>

        <div className="p-4 flex-1 flex flex-col justify-between">
          <div>
            <div className="text-[10px] uppercase font-extrabold text-amber-700 tracking-wider mb-1">
              Advertisement
            </div>
            <h3 className="text-sm font-black text-slate-900 line-clamp-1 mb-1.5">{ad.title}</h3>
            <p className="text-xs text-slate-600 line-clamp-2 leading-relaxed mb-4">{ad.description}</p>
          </div>

          <div className="flex items-center gap-2 pt-2 border-t border-slate-100">
            <a
              href={`tel:${cleanPhone}`}
              className="flex-1 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-900 text-xs font-bold flex items-center justify-center gap-1.5 transition-all"
            >
              <Phone className="w-3.5 h-3.5 text-cyan-700" />
              <span>Call</span>
            </a>
            <a
              href={whatsappUrl}
              target="_blank"
              rel="noreferrer"
              className="flex-1 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-md shadow-emerald-600/20 transition-all hover:scale-102"
            >
              <MessageCircle className="w-3.5 h-3.5" />
              <span>WhatsApp</span>
            </a>
            {ad.targetUrl && (
              <a
                href={ad.targetUrl}
                target="_blank"
                rel="noreferrer"
                className="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all"
                title="Visit Website"
              >
                <ExternalLink className="w-3.5 h-3.5" />
              </a>
            )}
          </div>
        </div>
      </div>
    );
  }

  // Default: Full Banner Style
  return (
    <div className="relative rounded-3xl bg-gradient-to-r from-amber-500/15 via-orange-500/10 to-cyan-500/15 border-2 border-amber-300 p-4 sm:p-6 shadow-[0_10px_35px_-5px_rgba(245,158,11,0.18)] overflow-hidden">
      <div className="absolute top-0 right-0 transform translate-x-4 -translate-y-4 w-28 h-28 bg-amber-400/20 rounded-full blur-xl pointer-events-none" />
      <div className="relative flex flex-col md:flex-row items-center justify-between gap-6">
        <div className="flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
          <div className="relative shrink-0">
            <img
              src={ad.imageUrl}
              alt={ad.title}
              className="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border-2 border-amber-300 shadow-md"
            />
            <span className="absolute -top-2 -left-2 px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 font-black text-[9px] uppercase tracking-wider shadow-xs flex items-center gap-0.5">
              <Sparkles className="w-2.5 h-2.5 fill-slate-950" /> Sponsored
            </span>
          </div>

          <div>
            <div className="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-1.5">
              <span className="px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 border border-amber-300 text-[10px] font-black uppercase tracking-wider">
                Official Advertisement
              </span>
              <span className="text-xs text-slate-600 font-semibold flex items-center gap-1">
                <MapPin className="w-3.5 h-3.5 text-cyan-600" /> {ad.location}
              </span>
            </div>

            <h3 className="text-base sm:text-lg font-black text-slate-950 leading-tight mb-1">
              {ad.title}
            </h3>
            <p className="text-xs text-slate-600 max-w-xl leading-relaxed">
              {ad.description}
            </p>
          </div>
        </div>

        <div className="flex flex-wrap items-center justify-center gap-2.5 shrink-0">
          <a
            href={`tel:${cleanPhone}`}
            className="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-900 border border-slate-200 text-xs font-bold flex items-center gap-1.5 shadow-xs transition-all hover:scale-102"
          >
            <Phone className="w-4 h-4 text-cyan-700" />
            <span>Call {ad.phone}</span>
          </a>

          <a
            href={whatsappUrl}
            target="_blank"
            rel="noreferrer"
            className="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-black flex items-center gap-2 shadow-lg shadow-emerald-600/25 transition-all hover:scale-102"
          >
            <MessageCircle className="w-4 h-4" />
            <span>WhatsApp Direct</span>
          </a>

          {ad.targetUrl && (
            <a
              href={ad.targetUrl}
              target="_blank"
              rel="noreferrer"
              className="p-2.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold transition-all shadow-xs"
              title="Open Website Link"
            >
              <ExternalLink className="w-4 h-4" />
            </a>
          )}
        </div>
      </div>
    </div>
  );
};
