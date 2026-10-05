import React, { useState, useMemo } from 'react';
import { Search, MapPin, Star, Phone, MessageCircle, FileText, PlusCircle, CheckCircle2, Briefcase, Filter, Sparkles } from 'lucide-react';
import { DigitalSkillProfile, UserProfile, District, Tehsil, AreaLocation } from '../types';
import { INITIAL_DISTRICTS, INITIAL_TEHSILS } from '../data/locationData';

interface DigitalSkillsSectionProps {
  skills: DigitalSkillProfile[];
  currentUser: UserProfile | null;
  onSelectSkill: (skill: DigitalSkillProfile) => void;
  onOpenPostSkill: () => void;
  onOpenPromoteSkill?: (skill: DigitalSkillProfile) => void;
  tehsils?: Tehsil[];
}

export const DigitalSkillsSection: React.FC<DigitalSkillsSectionProps> = ({
  skills,
  currentUser,
  onSelectSkill,
  onOpenPostSkill,
  onOpenPromoteSkill,
  tehsils = INITIAL_TEHSILS,
}) => {
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<string>('all');
  const [selectedTehsil, setSelectedTehsil] = useState<string>('all');
  const [selectedExperience, setSelectedExperience] = useState<string>('all');
  const [featuredOnly, setFeaturedOnly] = useState<boolean>(false);

  // Filter skills
  const filteredSkills = useMemo(() => {
    return skills.filter((s) => {
      // Must be approved
      if (s.status !== 'approved') return false;

      // Featured filter
      if (featuredOnly && !s.isFeatured) return false;

      // Category
      if (selectedCategory !== 'all' && s.category !== selectedCategory) return false;

      // Tehsil
      if (selectedTehsil !== 'all' && s.tehsilName !== selectedTehsil) return false;

      // Search Query
      if (searchQuery.trim()) {
        const q = searchQuery.toLowerCase();
        const matchesName = s.fullName.toLowerCase().includes(q);
        const matchesTitle = s.professionalTitle.toLowerCase().includes(q);
        const matchesSkill = s.mainSkill.toLowerCase().includes(q);
        const matchesSkillsList = s.skills.some((sk) => sk.toLowerCase().includes(q));
        const matchesLocation = (s.areaName || '').toLowerCase().includes(q) || (s.tehsilName || '').toLowerCase().includes(q);
        if (!matchesName && !matchesTitle && !matchesSkill && !matchesSkillsList && !matchesLocation) {
          return false;
        }
      }

      return true;
    });
  }, [skills, searchQuery, selectedCategory, selectedTehsil, selectedExperience, featuredOnly]);

  const featuredSkillsList = skills.filter((s) => s.status === 'approved' && s.isFeatured);

  return (
    <div className="py-8 px-4 max-w-7xl mx-auto space-y-8">
      {/* Hero Banner for Digital Skills */}
      <div className="rounded-3xl bg-gradient-to-r from-slate-950 via-slate-900 to-cyan-950 p-6 sm:p-10 text-white relative overflow-hidden shadow-2xl border border-cyan-500/30">
        <div className="relative z-10 max-w-3xl">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/20 text-cyan-300 text-xs font-bold border border-cyan-500/30 mb-4">
            <Sparkles className="w-3.5 h-3.5 text-cyan-400" /> Sargodha Division Freelancers & Tech Talent
          </div>

          <h1 className="text-2xl sm:text-4xl font-black tracking-tight text-white mb-3">
            Hire Top <span className="bg-gradient-to-r from-cyan-400 via-blue-400 to-fuchsia-400 bg-clip-text text-transparent">Digital Creators</span> in Sargodha
          </h1>
          <p className="text-xs sm:text-sm text-cyan-100/90 leading-relaxed mb-6 max-w-2xl">
            Connect directly with verified Website Designers, Video Editors, Developers, and Digital Marketers across
            Sargodha, Shaheenabad, and Sillanwali. Direct call & WhatsApp contact with zero platform deduction!
          </p>

          <div className="flex flex-wrap items-center gap-3">
            <button
              onClick={onOpenPostSkill}
              className="px-5 py-3 rounded-2xl bg-gradient-to-r from-cyan-500 via-blue-600 to-cyan-500 hover:from-cyan-400 hover:to-blue-500 text-white font-black text-xs sm:text-sm shadow-lg shadow-cyan-500/30 flex items-center gap-2 transition-all hover:scale-102"
            >
              <PlusCircle className="w-4 h-4" /> Create My Digital CV & Profile
            </button>
            <div className="text-xs text-slate-300 flex items-center gap-1.5 font-medium">
              <span>⭐ Featured placement available for Rs. 1,000 / 15 Days</span>
            </div>
          </div>
        </div>
      </div>

      {/* Featured Digital Skills Strip (Promoted) */}
      {featuredSkillsList.length > 0 && !featuredOnly && (
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <span className="p-1.5 rounded-xl bg-amber-400 text-slate-950 font-black text-xs">
                ⭐ FEATURED
              </span>
              <h2 className="text-base sm:text-lg font-black text-slate-900">
                Top Promoted Digital Talents
              </h2>
            </div>
            <span className="text-xs text-slate-500 font-bold">Priority Visibility</span>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            {featuredSkillsList.map((skill) => (
              <div
                key={`feat-${skill.id}`}
                className="rounded-3xl p-5 bg-gradient-to-b from-amber-50/70 via-white to-white border-2 border-amber-300 shadow-[0_10px_30px_rgba(245,158,11,0.15)] flex flex-col justify-between hover:-translate-y-1 transition-all"
              >
                <div>
                  <div className="flex items-start justify-between gap-3 mb-3">
                    <div className="flex items-center gap-3">
                      <img
                        src={skill.profilePhoto}
                        alt={skill.fullName}
                        className="w-14 h-14 rounded-2xl object-cover border-2 border-amber-400 shrink-0"
                      />
                      <div>
                        <div className="flex items-center gap-1.5">
                          <h3 className="text-sm font-black text-slate-900">{skill.fullName}</h3>
                          <span className="px-1.5 py-0.5 rounded-md bg-amber-400 text-slate-950 text-[9px] font-black uppercase">
                            ⭐ FEATURED
                          </span>
                        </div>
                        <div className="text-xs font-bold text-cyan-800 line-clamp-1">{skill.professionalTitle}</div>
                        <div className="text-[11px] text-slate-500 font-medium">{skill.experience} Experience</div>
                      </div>
                    </div>
                  </div>

                  <p className="text-xs text-slate-600 line-clamp-2 mb-3 leading-relaxed">
                    {skill.about}
                  </p>

                  <div className="flex flex-wrap gap-1 mb-4">
                    {skill.skills.slice(0, 4).map((s, idx) => (
                      <span key={idx} className="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-bold">
                        {s}
                      </span>
                    ))}
                  </div>
                </div>

                <div className="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                  <div className="text-xs font-bold text-slate-700 flex items-center gap-1">
                    <MapPin className="w-3.5 h-3.5 text-rose-500 shrink-0" />
                    <span>{skill.tehsilName || 'Sargodha'}</span>
                  </div>

                  <button
                    onClick={() => onSelectSkill(skill)}
                    className="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-1 shadow-sm transition-all"
                  >
                    <FileText className="w-3.5 h-3.5" /> View Digital CV
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Search & Filter Toolbar */}
      <div className="p-5 rounded-3xl bg-white border border-cyan-100 shadow-sm space-y-4">
        <div className="flex flex-col sm:flex-row items-center gap-3">
          <div className="relative flex-1 w-full">
            <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Search by skill, e.g. Website Designer, Video Editor, Graphic Designer..."
              className="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-bold text-slate-900 focus:outline-hidden focus:border-cyan-500"
            />
          </div>

          <div className="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <select
              value={selectedCategory}
              onChange={(e) => setSelectedCategory(e.target.value)}
              className="px-3 py-2.5 rounded-2xl border border-slate-200 text-xs font-bold text-slate-800 bg-white"
            >
              <option value="all">All Categories</option>
              <option value="Design & Creative">Design & Creative</option>
              <option value="Development & IT">Development & IT</option>
              <option value="Video & Animation">Video & Animation</option>
              <option value="Writing & Translation">Writing & Translation</option>
              <option value="Digital Marketing">Digital Marketing</option>
            </select>

            <select
              value={selectedTehsil}
              onChange={(e) => setSelectedTehsil(e.target.value)}
              className="px-3 py-2.5 rounded-2xl border border-slate-200 text-xs font-bold text-slate-800 bg-white"
            >
              <option value="all">All Tehsils</option>
              {tehsils.map((t) => (
                <option key={t.id} value={t.name}>{t.name}</option>
              ))}
            </select>

            <button
              onClick={() => setFeaturedOnly(!featuredOnly)}
              className={`px-3 py-2 rounded-2xl text-xs font-bold flex items-center gap-1.5 transition-all ${
                featuredOnly
                  ? 'bg-amber-400 text-slate-950 font-black shadow-xs'
                  : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
              }`}
            >
              <Star className="w-3.5 h-3.5 fill-current" />
              ⭐ Featured Only
            </button>
          </div>
        </div>

        <div className="text-xs text-slate-500 font-medium">
          Showing <strong>{filteredSkills.length}</strong> verified digital profiles in Sargodha Division
        </div>
      </div>

      {/* Main Grid of Digital Skills */}
      {filteredSkills.length === 0 ? (
        <div className="p-12 text-center rounded-3xl bg-white border border-slate-200 space-y-3">
          <div className="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-slate-400 text-xl">
            🔍
          </div>
          <h3 className="text-base font-bold text-slate-800">No digital creators found matching your criteria</h3>
          <p className="text-xs text-slate-500 max-w-sm mx-auto">
            Try adjusting your search keywords or switching tehsil filter to All Tehsils.
          </p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {filteredSkills.map((skill) => (
            <div
              key={skill.id}
              className={`rounded-3xl p-5 bg-white border flex flex-col justify-between transition-all duration-200 hover:-translate-y-1 ${
                skill.isFeatured
                  ? 'border-amber-400 shadow-[0_10px_30px_rgba(245,158,11,0.12)]'
                  : 'border-cyan-100 hover:border-cyan-300 shadow-sm hover:shadow-md'
              }`}
            >
              <div>
                <div className="flex items-start justify-between gap-3 mb-3">
                  <div className="flex items-center gap-3">
                    <img
                      src={skill.profilePhoto}
                      alt={skill.fullName}
                      className="w-14 h-14 rounded-2xl object-cover border border-slate-200 shrink-0"
                    />
                    <div>
                      <div className="flex items-center gap-1.5">
                        <h3 className="text-sm font-black text-slate-900">{skill.fullName}</h3>
                        {skill.isFeatured && (
                          <span className="px-1.5 py-0.5 rounded-md bg-amber-400 text-slate-950 text-[9px] font-black uppercase">
                            ⭐ FEATURED
                          </span>
                        )}
                      </div>
                      <div className="text-xs font-bold text-cyan-700 line-clamp-1">{skill.professionalTitle}</div>
                      <div className="text-[11px] text-slate-500 font-medium">
                        {skill.experience} • <span className="text-emerald-700 font-bold">{skill.availability}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <p className="text-xs text-slate-600 line-clamp-3 mb-4 leading-relaxed">
                  {skill.about}
                </p>

                <div className="flex flex-wrap gap-1.5 mb-4">
                  {skill.skills.slice(0, 5).map((s, idx) => (
                    <span key={idx} className="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-bold">
                      {s}
                    </span>
                  ))}
                  {skill.skills.length > 5 && (
                    <span className="px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[10px] font-bold">
                      +{skill.skills.length - 5}
                    </span>
                  )}
                </div>
              </div>

              <div className="pt-3 border-t border-slate-100 space-y-3">
                <div className="flex items-center justify-between text-xs">
                  <div className="flex items-center gap-1 text-slate-600 font-medium">
                    <MapPin className="w-3.5 h-3.5 text-rose-500 shrink-0" />
                    <span className="line-clamp-1">{skill.areaName || skill.tehsilName || 'Sargodha'}</span>
                  </div>

                  {skill.startingRate ? (
                    <div className="font-extrabold text-slate-900 text-xs">
                      Rs. {skill.startingRate.toLocaleString()} <span className="text-[10px] text-slate-400 font-normal">start</span>
                    </div>
                  ) : null}
                </div>

                <div className="flex items-center gap-2">
                  <button
                    onClick={() => onSelectSkill(skill)}
                    className="flex-1 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm transition-all"
                  >
                    <FileText className="w-3.5 h-3.5" /> View Digital CV
                  </button>

                  <a
                    href={`https://wa.me/92${skill.whatsapp.replace(/^0/, '')}`}
                    target="_blank"
                    rel="noreferrer"
                    className="p-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white shadow-xs transition-colors"
                    title="Chat on WhatsApp"
                  >
                    <MessageCircle className="w-4 h-4" />
                  </a>

                  <a
                    href={`tel:${skill.phone}`}
                    className="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors"
                    title="Call directly"
                  >
                    <Phone className="w-4 h-4" />
                  </a>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};
