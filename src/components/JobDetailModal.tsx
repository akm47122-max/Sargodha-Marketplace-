import React, { useState } from 'react';
import { X, Briefcase, Phone, MessageCircle, MapPin, Clock, Banknote, Share2, Flag, Copy, Check, ExternalLink } from 'lucide-react';
import { JobPost } from '../types';

interface JobDetailModalProps {
  job: JobPost | null;
  isOpen: boolean;
  onClose: () => void;
  onReport: (job: JobPost) => void;
}

export const JobDetailModal: React.FC<JobDetailModalProps> = ({
  job,
  isOpen,
  onClose,
  onReport,
}) => {
  const [copied, setCopied] = useState(false);
  const [showShare, setShowShare] = useState(false);

  if (!isOpen || !job) return null;

  const handleCopyLink = () => {
    navigator.clipboard.writeText(window.location.href);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  const shareText = `Check out "${job.title}" (${job.postType === 'need_worker' ? 'Hiring' : 'Job Wanted'}) on SargodhaMart in ${job.city}: ${window.location.href}`;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-md overflow-y-auto">
      <div className="relative w-full max-w-2xl bg-white border border-blue-200 rounded-3xl p-6 md:p-8 shadow-[0_20px_60px_-15px_rgba(37,99,235,0.25)] my-8 max-h-[90vh] overflow-y-auto">
        <button
          onClick={onClose}
          className="absolute top-5 right-5 p-2 rounded-xl text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-all"
        >
          <X className="w-5 h-5" />
        </button>

        {/* Header Badges */}
        <div className="flex items-center gap-2 mb-3">
          <span
            className={`px-3 py-1 rounded-xl text-xs font-black uppercase tracking-wider ${
              job.postType === 'need_worker'
                ? 'bg-blue-100 text-blue-800 border border-blue-300'
                : 'bg-cyan-100 text-cyan-800 border border-cyan-300'
            }`}
          >
            {job.postType === 'need_worker' ? '🏢 Hiring Worker' : '👤 Seeking Work'}
          </span>
          <span className="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-xl border border-slate-200">
            {job.category}
          </span>
          <span className="ml-auto text-xs text-slate-500 font-bold flex items-center gap-1">
            <MapPin className="w-3.5 h-3.5 text-blue-600" /> {job.city} ({job.area})
          </span>
        </div>

        {/* Title */}
        <h2 className="text-2xl font-black text-slate-900 mb-4">{job.title}</h2>

        {/* Compensation & Timing Badges */}
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
          {job.salaryOrPayment && (
            <div className="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                <Banknote className="w-5 h-5" />
              </div>
              <div>
                <div className="text-[10px] font-bold text-emerald-800 uppercase tracking-wider">Salary / Payment</div>
                <div className="text-sm font-black text-emerald-900">{job.salaryOrPayment}</div>
              </div>
            </div>
          )}

          {job.workingHours && (
            <div className="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold">
                <Clock className="w-5 h-5 text-blue-600" />
              </div>
              <div>
                <div className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Timing & Hours</div>
                <div className="text-sm font-bold text-slate-900">{job.workingHours}</div>
              </div>
            </div>
          )}
        </div>

        {/* Skills & Experience */}
        <div className="p-4 rounded-2xl bg-slate-50 border border-slate-200 mb-6 space-y-2 text-xs">
          <div className="flex items-center gap-2">
            <strong className="text-slate-800 font-bold">Required / Offered Skills:</strong>
            <span className="text-slate-700 font-semibold">{job.skills}</span>
          </div>
          {job.experience && (
            <div className="flex items-center gap-2">
              <strong className="text-slate-800 font-bold">Experience Level:</strong>
              <span className="text-slate-700 font-semibold">{job.experience}</span>
            </div>
          )}
          <div className="flex items-center gap-2">
            <strong className="text-slate-800 font-bold">Posted By:</strong>
            <span className="text-slate-700 font-semibold">{job.posterName}</span>
          </div>
        </div>

        {/* Description */}
        <div className="mb-6">
          <h4 className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Job Description & Details</h4>
          <p className="text-slate-700 text-xs md:text-sm leading-relaxed whitespace-pre-line font-medium bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
            {job.description}
          </p>
        </div>

        {/* Direct Contact Actions */}
        <div className="space-y-3 pt-4 border-t border-slate-100">
          <div className="grid grid-cols-2 gap-3">
            <a
              href={`tel:${job.phone}`}
              className="py-3 px-4 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 flex items-center justify-center gap-2 transition-all shadow-xs"
            >
              <Phone className="w-4 h-4 text-blue-600" />
              <span>Call ({job.phone})</span>
            </a>
            <a
              href={`https://wa.me/92${job.whatsapp.replace(/^0/, '')}?text=${encodeURIComponent(`Salam! I saw your job post "${job.title}" on SargodhaMart.`)}`}
              target="_blank"
              rel="noreferrer"
              className="py-3 px-4 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white flex items-center justify-center gap-2 transition-all shadow-lg shadow-emerald-600/25"
            >
              <MessageCircle className="w-4 h-4" />
              <span>WhatsApp Chat</span>
            </a>
          </div>

          {/* Social Share & Report */}
          <div className="flex items-center justify-between text-xs pt-1">
            <button
              onClick={() => setShowShare(!showShare)}
              className="text-slate-600 hover:text-blue-700 font-semibold flex items-center gap-1.5 transition-colors"
            >
              <Share2 className="w-4 h-4 text-blue-600" />
              <span>Share Job Posting</span>
            </button>

            <button
              onClick={() => {
                onClose();
                onReport(job);
              }}
              className="text-slate-500 hover:text-rose-600 font-semibold flex items-center gap-1 transition-colors"
            >
              <Flag className="w-3.5 h-3.5" />
              <span>Report this post</span>
            </button>
          </div>

          {/* Share Options Popup */}
          {showShare && (
            <div className="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-2 shadow-sm">
              <div className="flex items-center gap-2">
                <a
                  href={`https://wa.me/?text=${encodeURIComponent(shareText)}`}
                  target="_blank"
                  rel="noreferrer"
                  className="px-2.5 py-1 rounded bg-emerald-600 text-white text-[11px] font-bold"
                >
                  WhatsApp
                </a>
                <a
                  href={`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}`}
                  target="_blank"
                  rel="noreferrer"
                  className="px-2.5 py-1 rounded bg-blue-600 text-white text-[11px] font-bold"
                >
                  Facebook
                </a>
              </div>
              <button
                onClick={handleCopyLink}
                className="px-2.5 py-1 rounded bg-white border border-slate-200 text-slate-700 text-[11px] font-bold flex items-center gap-1 shadow-xs"
              >
                {copied ? <Check className="w-3 h-3 text-emerald-600" /> : <Copy className="w-3 h-3" />}
                {copied ? 'Copied' : 'Copy Direct Link'}
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};
