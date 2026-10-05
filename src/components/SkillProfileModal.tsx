import React from 'react';
import { X, MapPin, Phone, MessageCircle, Mail, ExternalLink, Download, FileText, CheckCircle2, Star, Briefcase, Award, Clock } from 'lucide-react';
import { DigitalSkillProfile } from '../types';

interface SkillProfileModalProps {
  skill: DigitalSkillProfile | null;
  onClose: () => void;
  onPromoteClick?: (skill: DigitalSkillProfile) => void;
  isOwner?: boolean;
}

export const SkillProfileModal: React.FC<SkillProfileModalProps> = ({
  skill,
  onClose,
  onPromoteClick,
  isOwner = false,
}) => {
  if (!skill) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md animate-in fade-in duration-200">
      <div className="relative w-full max-w-3xl bg-white rounded-3xl shadow-2xl border border-cyan-100 overflow-hidden flex flex-col max-h-[92vh]">
        {/* CV Header Hero */}
        <div className="p-6 bg-gradient-to-r from-slate-950 via-slate-900 to-cyan-950 text-white relative">
          <button
            onClick={onClose}
            className="absolute top-4 right-4 w-8 h-8 rounded-full bg-slate-800/80 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition-colors border border-slate-700 z-10"
          >
            <X className="w-4 h-4" />
          </button>

          <div className="flex flex-col sm:flex-row items-start sm:items-center gap-5">
            <div className="relative">
              <img
                src={skill.profilePhoto}
                alt={skill.fullName}
                className="w-20 h-20 sm:w-24 sm:h-24 rounded-3xl object-cover border-4 border-cyan-400/40 shadow-xl shadow-cyan-500/20"
              />
              {skill.isFeatured && (
                <span className="absolute -top-2 -right-2 px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[10px] font-black uppercase shadow-md flex items-center gap-1">
                  <Star className="w-3 h-3 fill-slate-950" /> FEATURED
                </span>
              )}
            </div>

            <div className="flex-1">
              <div className="flex flex-wrap items-center gap-2 mb-1">
                <h1 className="text-xl sm:text-2xl font-black text-white">{skill.fullName}</h1>
                <span className="px-2 py-0.5 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-[10px] font-extrabold flex items-center gap-1">
                  <CheckCircle2 className="w-3 h-3 text-emerald-400" /> Verified Member
                </span>
                <span className="px-2 py-0.5 rounded-full bg-cyan-500/20 border border-cyan-500/40 text-cyan-300 text-[10px] font-bold">
                  {skill.availability}
                </span>
              </div>

              <div className="text-sm font-bold text-cyan-300 mb-2">{skill.professionalTitle}</div>

              <div className="flex flex-wrap items-center gap-3 text-xs text-slate-300">
                <span className="flex items-center gap-1">
                  <Briefcase className="w-3.5 h-3.5 text-cyan-400" />
                  {skill.mainSkill} ({skill.experience})
                </span>
                <span>•</span>
                <span className="flex items-center gap-1">
                  <MapPin className="w-3.5 h-3.5 text-rose-400" />
                  {skill.areaName || 'Sargodha'}, {skill.tehsilName || 'Sargodha Tehsil'}
                </span>
                {skill.startingRate && (
                  <>
                    <span>•</span>
                    <span className="text-amber-300 font-extrabold">
                      From Rs. {skill.startingRate.toLocaleString()}
                    </span>
                  </>
                )}
              </div>
            </div>
          </div>
        </div>

        {/* Action Button Bar */}
        <div className="px-6 py-3 bg-slate-100 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
          <div className="flex items-center gap-2">
            <a
              href={`tel:${skill.phone}`}
              className="px-3.5 py-1.5 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 flex items-center gap-1.5 shadow-sm transition-all"
            >
              <Phone className="w-3.5 h-3.5" /> Call ({skill.phone})
            </a>
            <a
              href={`https://wa.me/92${skill.whatsapp.replace(/^0/, '')}`}
              target="_blank"
              rel="noreferrer"
              className="px-3.5 py-1.5 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-500 flex items-center gap-1.5 shadow-sm transition-all"
            >
              <MessageCircle className="w-3.5 h-3.5" /> WhatsApp
            </a>
            {skill.showEmail && skill.email && (
              <a
                href={`mailto:${skill.email}`}
                className="px-3.5 py-1.5 rounded-xl bg-cyan-700 text-white font-bold text-xs hover:bg-cyan-600 flex items-center gap-1.5 shadow-sm transition-all"
              >
                <Mail className="w-3.5 h-3.5" /> Email
              </a>
            )}
          </div>

          <div className="flex items-center gap-2">
            {skill.cvFilePath && (
              <a
                href={skill.cvFilePath}
                download
                target="_blank"
                rel="noreferrer"
                className="px-3.5 py-1.5 rounded-xl bg-white border border-slate-300 text-slate-800 font-bold text-xs hover:bg-slate-50 flex items-center gap-1.5 shadow-xs transition-all"
              >
                <Download className="w-3.5 h-3.5 text-cyan-600" /> Download CV (PDF)
              </a>
            )}

            {isOwner && onPromoteClick && !skill.isFeatured && (
              <button
                onClick={() => onPromoteClick(skill)}
                className="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-black text-xs flex items-center gap-1 shadow-md shadow-orange-500/25 transition-all"
              >
                ⭐ Feature My Skill (Rs. 1,000 / 15 Days)
              </button>
            )}
          </div>
        </div>

        {/* Digital CV Body Content */}
        <div className="p-6 overflow-y-auto space-y-6">
          {/* About / Bio */}
          <div>
            <h3 className="text-xs font-black text-slate-800 uppercase tracking-wider mb-2 flex items-center gap-1.5">
              <Award className="w-4 h-4 text-cyan-600" /> Professional Summary
            </h3>
            <p className="text-xs sm:text-sm text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-2xl border border-slate-200">
              {skill.about}
            </p>
          </div>

          {/* Core Skills & Tools Tag Cloud */}
          <div>
            <h3 className="text-xs font-black text-slate-800 uppercase tracking-wider mb-2">
              Core Skills & Technologies
            </h3>
            <div className="flex flex-wrap gap-1.5">
              {skill.skills.map((s, idx) => (
                <span
                  key={idx}
                  className="px-3 py-1 rounded-xl bg-cyan-50 border border-cyan-200 text-cyan-900 text-xs font-bold shadow-2xs"
                >
                  {s}
                </span>
              ))}
            </div>
          </div>

          {/* Services Offered */}
          <div>
            <h3 className="text-xs font-black text-slate-800 uppercase tracking-wider mb-2">
              Services Offered
            </h3>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
              {skill.servicesOffered.map((service, idx) => (
                <div
                  key={idx}
                  className="p-3 rounded-2xl bg-white border border-slate-200 flex items-start gap-2 text-xs font-bold text-slate-800 shadow-xs"
                >
                  <CheckCircle2 className="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" />
                  <span>{service}</span>
                </div>
              ))}
            </div>
          </div>

          {/* Portfolio & Project Links */}
          {skill.portfolioLinks && skill.portfolioLinks.length > 0 && (
            <div>
              <h3 className="text-xs font-black text-slate-800 uppercase tracking-wider mb-2">
                Portfolio & Work Samples
              </h3>
              <div className="flex flex-wrap gap-2">
                {skill.portfolioLinks.map((p, idx) => (
                  <a
                    key={idx}
                    href={p.url}
                    target="_blank"
                    rel="noreferrer"
                    className="px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 flex items-center gap-1.5 shadow-sm transition-all"
                  >
                    <ExternalLink className="w-3.5 h-3.5 text-cyan-400" />
                    <span>{p.label}</span>
                  </a>
                ))}
              </div>
            </div>
          )}

          {/* Location & Contact Notice */}
          <div className="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-600 flex items-center justify-between">
            <div>
              <strong>Based in:</strong> {skill.areaName || skill.tehsilName || 'Sargodha'}, Punjab, Pakistan.
            </div>
            <div className="text-[11px] text-slate-500">Direct hiring • Zero middleman commission</div>
          </div>
        </div>
      </div>
    </div>
  );
};
