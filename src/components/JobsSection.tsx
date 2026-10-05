import React, { useState, useMemo } from 'react';
import { Briefcase, UserCheck, Search, MapPin, Phone, MessageCircle, PlusCircle, Flag, Clock, Banknote, Shield, Filter, X } from 'lucide-react';
import { JobPost, JobPostType, City, District, Tehsil, AreaLocation } from '../types';
import { INITIAL_DISTRICTS, INITIAL_TEHSILS, INITIAL_AREAS } from '../data/locationData';

interface JobsSectionProps {
  jobs: JobPost[];
  onPostJobClick: () => void;
  onReportJob: (job: JobPost) => void;
  onSelectJob?: (job: JobPost) => void;
  selectedCity?: City | 'All';
  setSelectedCity?: (city: City | 'All') => void;
  districts?: District[];
  tehsils?: Tehsil[];
  areas?: AreaLocation[];
  selectedDistrictId?: number | 'all';
  onSelectDistrictId?: (id: number | 'all') => void;
  selectedTehsilId?: number | 'all';
  onSelectTehsilId?: (id: number | 'all') => void;
  selectedArea?: string;
  onSelectArea?: (area: string) => void;
}

export const JobsSection: React.FC<JobsSectionProps> = ({
  jobs,
  onPostJobClick,
  onReportJob,
  onSelectJob,
  selectedCity = 'All',
  setSelectedCity,
  districts = INITIAL_DISTRICTS,
  tehsils = INITIAL_TEHSILS,
  areas = INITIAL_AREAS,
  selectedDistrictId,
  onSelectDistrictId,
  selectedTehsilId,
  onSelectTehsilId,
  selectedArea,
  onSelectArea,
}) => {
  const [activeTab, setActiveTab] = useState<'all' | JobPostType>('all');
  const [jobSearch, setJobSearch] = useState('');

  // Local state fallbacks if not controlled by parent
  const [internalDistrictId, setInternalDistrictId] = useState<number | 'all'>('all');
  const [internalTehsilId, setInternalTehsilId] = useState<number | 'all'>('all');
  const [internalArea, setInternalArea] = useState<string>('all');

  const activeDistrictId = selectedDistrictId !== undefined ? selectedDistrictId : internalDistrictId;
  const setDistrictId = (val: number | 'all') => {
    if (onSelectDistrictId) onSelectDistrictId(val);
    else setInternalDistrictId(val);
    // reset tehsil and area
    if (onSelectTehsilId) onSelectTehsilId('all');
    else setInternalTehsilId('all');
    if (onSelectArea) onSelectArea('all');
    else setInternalArea('all');
  };

  const activeTehsilId = selectedTehsilId !== undefined ? selectedTehsilId : internalTehsilId;
  const setTehsilId = (val: number | 'all') => {
    if (onSelectTehsilId) onSelectTehsilId(val);
    else setInternalTehsilId(val);
    if (onSelectArea) onSelectArea('all');
    else setInternalArea('all');
  };

  const activeArea = selectedArea !== undefined ? selectedArea : internalArea;
  const setArea = (val: string) => {
    if (onSelectArea) onSelectArea(val);
    else setInternalArea(val);
  };

  // Available Tehsils based on selected District
  const availableTehsils = useMemo(() => {
    if (activeDistrictId === 'all') return tehsils.filter((t) => t.isActive);
    return tehsils.filter((t) => t.districtId === activeDistrictId && t.isActive);
  }, [tehsils, activeDistrictId]);

  // Available Areas based on selected Tehsil
  const availableAreas = useMemo(() => {
    if (activeTehsilId === 'all') return [];
    return areas.filter((a) => a.tehsilId === activeTehsilId && a.isActive);
  }, [areas, activeTehsilId]);

  const filteredJobs = jobs.filter((job) => {
    if (job.status !== 'published') return false;
    if (activeTab !== 'all' && job.postType !== activeTab) return false;

    // Filter by District
    if (activeDistrictId !== 'all') {
      const dist = districts.find((d) => d.id === activeDistrictId);
      const distName = dist?.name.toLowerCase();
      const matchDist =
        job.districtId === activeDistrictId ||
        (distName && (job.districtName?.toLowerCase() === distName || job.city.toLowerCase() === distName));
      if (!matchDist) return false;
    }

    // Filter by Tehsil
    if (activeTehsilId !== 'all') {
      const teh = tehsils.find((t) => t.id === activeTehsilId);
      const tehName = teh?.name.toLowerCase();
      const matchTeh =
        job.tehsilId === activeTehsilId ||
        (tehName && (
          job.tehsilName?.toLowerCase() === tehName ||
          job.city.toLowerCase() === tehName ||
          job.area.toLowerCase().includes(tehName)
        ));
      if (!matchTeh) return false;
    }

    // Filter by Area
    if (activeArea !== 'all' && activeArea.trim() !== '') {
      const qArea = activeArea.toLowerCase();
      const matchArea =
        job.area.toLowerCase().includes(qArea) ||
        (job.areaName && job.areaName.toLowerCase().includes(qArea));
      if (!matchArea) return false;
    }

    // Legacy city filter fallback if provided and not 'All'
    if (selectedCity && selectedCity !== 'All' && activeDistrictId === 'all') {
      if (job.city !== selectedCity) return false;
    }

    if (jobSearch.trim() !== '') {
      const q = jobSearch.toLowerCase();
      const match =
        job.title.toLowerCase().includes(q) ||
        job.skills.toLowerCase().includes(q) ||
        job.category.toLowerCase().includes(q) ||
        job.description.toLowerCase().includes(q) ||
        job.area.toLowerCase().includes(q) ||
        (job.districtName && job.districtName.toLowerCase().includes(q)) ||
        (job.tehsilName && job.tehsilName.toLowerCase().includes(q));
      if (!match) return false;
    }
    return true;
  });

  return (
    <section className="py-8 px-4 max-w-7xl mx-auto">
      {/* Banner - White Neon Gradient */}
      <div className="relative rounded-3xl overflow-hidden bg-gradient-to-r from-blue-50 via-cyan-50 to-indigo-50 border border-blue-200 p-6 md:p-10 mb-8 shadow-[0_10px_35px_-5px_rgba(37,99,235,0.12)]">
        <div className="relative z-10 max-w-3xl">
          <div className="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/90 border border-blue-300 text-blue-800 text-xs font-bold mb-4 shadow-[0_0_10px_rgba(37,99,235,0.15)]">
            <Briefcase className="w-3.5 h-3.5 text-blue-600" />
            Jobs & Employment Hub • Sargodha District
          </div>
          <h2 className="text-2xl md:text-4xl font-black text-slate-950 tracking-tight mb-3">
            Find Work or Hire Skilled Workers <br />
            <span className="bg-gradient-to-r from-cyan-600 via-blue-600 to-fuchsia-600 bg-clip-text text-transparent">
              In Sargodha, Shaheenabad & Sillanwali
            </span>
          </h2>
          <p className="text-slate-700 text-sm md:text-base mb-6 font-medium">
            Whether you are a master driver, dairy specialist, mobile technician, or a shop owner seeking reliable staff — connect directly with local talent without agency fees.
          </p>
          <div className="flex flex-wrap items-center gap-3">
            <button
              onClick={() => setActiveTab('need_job')}
              className={`px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 ${
                activeTab === 'need_job'
                  ? 'bg-cyan-600 text-white shadow-lg shadow-cyan-600/30'
                  : 'bg-white text-slate-700 hover:text-slate-950 border border-slate-200'
              }`}
            >
              <span>👤</span> I Need a Job (Work Wanted)
            </button>
            <button
              onClick={() => setActiveTab('need_worker')}
              className={`px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 ${
                activeTab === 'need_worker'
                  ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30'
                  : 'bg-white text-slate-700 hover:text-slate-950 border border-slate-200'
              }`}
            >
              <span>🏢</span> I Need a Worker (Hiring)
            </button>
            <button
              onClick={onPostJobClick}
              className="ml-auto px-4 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-fuchsia-600 to-purple-600 hover:from-fuchsia-500 hover:to-purple-500 text-white shadow-lg shadow-fuchsia-600/25 flex items-center gap-1.5"
            >
              <PlusCircle className="w-4 h-4" />
              Post Job / Worker Requirement
            </button>
          </div>
        </div>
      </div>

      {/* Filter and Search Bar - White Neon Glass */}
      <div className="bg-white/95 backdrop-blur-md border border-cyan-200 p-3 rounded-2xl mb-8 flex flex-col md:flex-row items-center gap-3 shadow-md shadow-cyan-500/5">
        <div className="relative flex-1 w-full">
          <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-cyan-600" />
          <input
            type="text"
            value={jobSearch}
            onChange={(e) => setJobSearch(e.target.value)}
            placeholder="Search by job title, skill (e.g. driver, milking, technician, sales)..."
            className="w-full pl-10 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs md:text-sm placeholder-slate-400 focus:outline-none focus:border-cyan-500 focus:bg-white transition-all font-medium"
          />
        </div>

        <div className="flex items-center gap-2 w-full md:w-auto">
          <div className="flex items-center gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200">
            <button
              onClick={() => setActiveTab('all')}
              className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all ${
                activeTab === 'all' ? 'bg-white text-cyan-800 shadow-xs border border-cyan-300' : 'text-slate-600 hover:text-slate-900'
              }`}
            >
              All ({jobs.filter((j) => j.status === 'published').length})
            </button>
            <button
              onClick={() => setActiveTab('need_worker')}
              className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all ${
                activeTab === 'need_worker' ? 'bg-white text-blue-800 shadow-xs border border-blue-300' : 'text-slate-600 hover:text-slate-900'
              }`}
            >
              Hiring ({jobs.filter((j) => j.status === 'published' && j.postType === 'need_worker').length})
            </button>
            <button
              onClick={() => setActiveTab('need_job')}
              className={`px-3 py-1.5 rounded-lg text-xs font-bold transition-all ${
                activeTab === 'need_job' ? 'bg-white text-cyan-800 shadow-xs border border-cyan-300' : 'text-slate-600 hover:text-slate-900'
              }`}
            >
              Seeking ({jobs.filter((j) => j.status === 'published' && j.postType === 'need_job').length})
            </button>
          </div>

          {/* Structured Location Filters */}
          <div className="flex flex-wrap items-center gap-2">
            {/* District Filter */}
            <select
              value={activeDistrictId}
              onChange={(e) => setDistrictId(e.target.value === 'all' ? 'all' : Number(e.target.value))}
              className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500 focus:bg-white"
            >
              <option value="all">📍 All Districts</option>
              {districts.filter((d) => d.isActive).map((d) => (
                <option key={d.id} value={d.id}>
                  {d.name}
                </option>
              ))}
            </select>

            {/* Tehsil Filter */}
            <select
              value={activeTehsilId}
              onChange={(e) => setTehsilId(e.target.value === 'all' ? 'all' : Number(e.target.value))}
              disabled={activeDistrictId === 'all'}
              className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500 focus:bg-white disabled:opacity-50"
            >
              <option value="all">🏛️ All Tehsils</option>
              {availableTehsils.map((t) => (
                <option key={t.id} value={t.id}>
                  {t.name}
                </option>
              ))}
            </select>

            {/* Area Filter */}
            {availableAreas.length > 0 && (
              <select
                value={activeArea}
                onChange={(e) => setArea(e.target.value)}
                className="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs font-semibold focus:outline-none focus:border-cyan-500 focus:bg-white"
              >
                <option value="all">🏡 All Areas</option>
                {availableAreas.map((a) => (
                  <option key={a.id} value={a.name}>
                    {a.name}
                  </option>
                ))}
              </select>
            )}

            {/* Reset Filter Button */}
            {(activeDistrictId !== 'all' || activeTehsilId !== 'all' || activeArea !== 'all') && (
              <button
                type="button"
                onClick={() => {
                  setDistrictId('all');
                  setTehsilId('all');
                  setArea('all');
                }}
                className="p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 text-xs font-bold transition-all"
                title="Reset location filter"
              >
                <X className="w-3.5 h-3.5" />
              </button>
            )}
          </div>
        </div>
      </div>

      {/* Jobs Grid */}
      {filteredJobs.length === 0 ? (
        <div className="py-16 text-center bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
          <Briefcase className="w-12 h-12 text-slate-400 mx-auto mb-3" />
          <h3 className="text-lg font-bold text-slate-900 mb-1">No Job Postings Found</h3>
          <p className="text-slate-500 text-xs mb-4">Try adjusting your search criteria or be the first to post!</p>
          <button
            onClick={onPostJobClick}
            className="px-4 py-2 rounded-xl text-xs font-bold bg-cyan-600 text-white hover:bg-cyan-500 transition-all shadow-md shadow-cyan-600/20"
          >
            Post a Job Now
          </button>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {filteredJobs.map((job) => (
            <div
              key={job.id}
              onClick={() => onSelectJob?.(job)}
              className="bg-white/95 backdrop-blur-md border border-cyan-200/90 rounded-2xl p-5 hover:border-cyan-400 hover:shadow-[0_10px_30px_rgba(6,182,212,0.15)] transition-all flex flex-col justify-between shadow-xs cursor-pointer group"
            >
              <div>
                {/* Header Badge */}
                <div className="flex items-center justify-between gap-2 mb-3">
                  <span
                    className={`px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider ${
                      job.postType === 'need_worker'
                        ? 'bg-blue-50 text-blue-700 border border-blue-200'
                        : 'bg-cyan-50 text-cyan-700 border border-cyan-200'
                    }`}
                  >
                    {job.postType === 'need_worker' ? '🏢 Hiring Worker' : '👤 Seeking Work'}
                  </span>
                  <div className="text-[11px] text-slate-500 font-semibold flex items-center gap-1">
                    <MapPin className="w-3 h-3 text-cyan-600" />
                    <span>{job.city}</span>
                  </div>
                </div>

                <h3 className="text-base font-bold text-slate-900 mb-2 line-clamp-2 group-hover:text-blue-700 transition-colors">{job.title}</h3>

                <p className="text-slate-600 text-xs line-clamp-3 mb-4 leading-relaxed font-medium">{job.description}</p>

                {/* Details list */}
                <div className="space-y-1.5 mb-4 text-xs">
                  <div className="flex items-center gap-2 text-slate-600">
                    <strong className="text-slate-800">Skills:</strong>
                    <span className="text-slate-700 truncate">{job.skills}</span>
                  </div>
                  {job.salaryOrPayment && (
                    <div className="flex items-center gap-2 text-slate-600">
                      <Banknote className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                      <span className="text-emerald-700 font-bold">{job.salaryOrPayment}</span>
                    </div>
                  )}
                  {job.workingHours && (
                    <div className="flex items-center gap-2 text-slate-600">
                      <Clock className="w-3.5 h-3.5 text-slate-500 shrink-0" />
                      <span className="text-slate-700">{job.workingHours}</span>
                    </div>
                  )}
                </div>
              </div>

              {/* Bottom Actions */}
              <div className="pt-3 border-t border-slate-100 flex items-center gap-2" onClick={(e) => e.stopPropagation()}>
                <a
                  href={`tel:${job.phone}`}
                  className="flex-1 py-1.5 px-3 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 flex items-center justify-center gap-1.5 transition-all shadow-xs"
                >
                  <Phone className="w-3.5 h-3.5 text-cyan-600" />
                  <span>Call</span>
                </a>
                <a
                  href={`https://wa.me/92${job.whatsapp.replace(/^0/, '')}?text=${encodeURIComponent(`Salam! I saw your job post "${job.title}" on SargodhaMart.`)}`}
                  target="_blank"
                  rel="noreferrer"
                  className="flex-1 py-1.5 px-3 rounded-xl text-xs font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center justify-center gap-1.5 transition-all shadow-[0_0_10px_rgba(16,185,129,0.15)]"
                >
                  <MessageCircle className="w-3.5 h-3.5 text-emerald-600" />
                  <span>WhatsApp</span>
                </a>
                <button
                  onClick={() => onReportJob(job)}
                  className="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-rose-600 border border-slate-200 transition-all"
                  title="Report Job Post"
                >
                  <Flag className="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </section>
  );
};
