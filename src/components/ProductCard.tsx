import React from 'react';
import { Phone, MessageCircle, Heart, MapPin, Sparkles, Eye, ShieldCheck, Share2 } from 'lucide-react';
import { Listing } from '../types';

interface ProductCardProps {
  listing: Listing;
  isFavorite: boolean;
  onToggleFavorite: (id: number) => void;
  onSelect: (listing: Listing) => void;
  onShare: (listing: Listing, e: React.MouseEvent) => void;
}

export const ProductCard: React.FC<ProductCardProps> = ({
  listing,
  isFavorite,
  onToggleFavorite,
  onSelect,
  onShare,
}) => {
  return (
    <div 
      onClick={() => onSelect(listing)}
      className="group relative bg-white/95 backdrop-blur-md border border-cyan-200/90 rounded-2xl overflow-hidden hover:border-cyan-400 hover:shadow-[0_12px_30px_rgba(6,182,212,0.2)] transition-all cursor-pointer flex flex-col h-full transform hover:-translate-y-1 shadow-sm"
    >
      {/* Image Area */}
      <div className="relative aspect-4/3 overflow-hidden bg-slate-100">
        <img
          src={listing.images[0]}
          alt={listing.title}
          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        />
        
        {/* Subtle Gradient Overlay */}
        <div className="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent"></div>

        {/* Featured Badge */}
        {listing.isFeatured && (
          <div className="absolute top-2.5 left-2.5 flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-gradient-to-r from-amber-400 to-orange-500 text-slate-950 shadow-lg shadow-orange-500/30">
            <span>⭐</span>
            FEATURED
          </div>
        )}

        {/* Condition Badge */}
        <div className="absolute bottom-2.5 left-2.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-white/95 text-slate-800 border border-slate-200 shadow-xs">
          {listing.condition}
        </div>

        {/* Photos Count */}
        <div className="absolute bottom-2.5 right-2.5 px-2 py-0.5 rounded text-[10px] font-bold bg-black/60 text-white backdrop-blur-xs">
          📷 {listing.images.length}/3
        </div>

        {/* Top Right Action Buttons */}
        <div className="absolute top-2.5 right-2.5 flex items-center gap-1.5 z-10">
          <button
            onClick={(e) => {
              e.stopPropagation();
              onShare(listing, e);
            }}
            className="w-7 h-7 rounded-full bg-white/90 hover:bg-white text-slate-700 flex items-center justify-center border border-slate-200 shadow-xs transition-all hover:text-cyan-700"
            title="Share Product"
          >
            <Share2 className="w-3.5 h-3.5" />
          </button>
          <button
            onClick={(e) => {
              e.stopPropagation();
              onToggleFavorite(listing.id);
            }}
            className={`w-7 h-7 rounded-full flex items-center justify-center border transition-all ${
              isFavorite
                ? 'bg-rose-50 border-rose-300 text-rose-600 shadow-[0_0_8px_rgba(244,63,94,0.3)]'
                : 'bg-white/90 hover:bg-white text-slate-700 border-slate-200 shadow-xs'
            }`}
            title="Add to Favorites"
          >
            <Heart className={`w-3.5 h-3.5 ${isFavorite ? 'fill-rose-500 text-rose-500' : ''}`} />
          </button>
        </div>
      </div>

      {/* Content Area */}
      <div className="p-4 flex flex-col flex-grow justify-between">
        <div>
          {/* Price & Location */}
          <div className="flex items-baseline justify-between gap-2 mb-1.5">
            <div className="text-lg font-black text-slate-900">
              <span className="text-cyan-700 text-xs font-bold mr-1">Rs.</span>
              {listing.price.toLocaleString()}
            </div>
            <div className="text-[11px] text-slate-600 font-semibold flex items-center gap-1 truncate max-w-[130px]">
              <MapPin className="w-3 h-3 text-cyan-600 shrink-0" />
              <span>{listing.city}</span>
            </div>
          </div>

          {/* Title */}
          <h3 className="text-sm font-bold text-slate-800 group-hover:text-cyan-700 line-clamp-2 transition-colors mb-2">
            {listing.title}
          </h3>

          {/* Category & Views */}
          <div className="flex items-center justify-between text-[11px] text-slate-500 mb-3 font-medium">
            <span className="truncate">{listing.categoryName}</span>
            <span className="flex items-center gap-1 text-slate-500 text-[10px]">
              <Eye className="w-3 h-3 text-cyan-600" /> {listing.views}
            </span>
          </div>
        </div>

        {/* Bottom Actions: Call & WhatsApp */}
        <div className="pt-3 border-t border-slate-100 flex items-center gap-2" onClick={(e) => e.stopPropagation()}>
          <a
            href={`tel:${listing.sellerPhone}`}
            className="flex-1 py-1.5 px-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 flex items-center justify-center gap-1.5 transition-all shadow-xs"
          >
            <Phone className="w-3.5 h-3.5 text-cyan-600" />
            <span>Call</span>
          </a>
          <a
            href={`https://wa.me/92${listing.sellerPhone.replace(/^0/, '')}?text=${encodeURIComponent(`Salam! I saw your listing "${listing.title}" on SargodhaMart. Is it still available?`)}`}
            target="_blank"
            rel="noreferrer"
            className="flex-1 py-1.5 px-2 rounded-xl text-xs font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center justify-center gap-1.5 transition-all shadow-[0_0_10px_rgba(16,185,129,0.15)]"
          >
            <MessageCircle className="w-3.5 h-3.5 text-emerald-600" />
            <span>WhatsApp</span>
          </a>
        </div>
      </div>
    </div>
  );
};
