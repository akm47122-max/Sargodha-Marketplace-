import React, { useState } from 'react';
import { X, Briefcase, Upload, AlertCircle, FileText, CheckCircle2, User, MapPin } from 'lucide-react';
import { DigitalSkillProfile, UserProfile, District, Tehsil, AreaLocation } from '../types';
import { INITIAL_DISTRICTS, INITIAL_TEHSILS, INITIAL_AREAS } from '../data/locationData';

interface PostSkillModalProps {
  isOpen: boolean;
  onClose: () => void;
  currentUser: UserProfile;
  initialSkill?: DigitalSkillProfile | null;
  districts?: District[];
  tehsils?: Tehsil[];
  areas?: AreaLocation[];
  onSaveSkill: (skillData: Omit<DigitalSkillProfile, 'id' | 'createdAt'>, skillId?: number) => void;
}

export const PostSkillModal: React.FC<PostSkillModalProps> = ({
  isOpen,
  onClose,
  currentUser,
  initialSkill,
  districts = INITIAL_DISTRICTS,
  tehsils = INITIAL_TEHSILS,
  areas = INITIAL_AREAS,
  onSaveSkill,
}) => {
  const [fullName, setFullName] = useState(initialSkill?.fullName || currentUser.name || '');
  const [professionalTitle, setProfessionalTitle] = useState(initialSkill?.professionalTitle || '');
  const [mainSkill, setMainSkill] = useState(initialSkill?.mainSkill || 'Website Designer');
  const [category, setCategory] = useState(initialSkill?.category || 'Design & Creative');
  const [skillsStr, setSkillsStr] = useState(initialSkill?.skills.join(', ') || 'Figma, Tailwind CSS, WordPress');
  const [experience, setExperience] = useState(initialSkill?.experience || '3 Years');
  const [about, setAbout] = useState(initialSkill?.about || '');
  
  // Location
  const [districtId, setDistrictId] = useState<number>(initialSkill?.districtId || 1);
  const [tehsilId, setTehsilId] = useState<number>(initialSkill?.tehsilId || 1);
  const [areaName, setAreaName] = useState(initialSkill?.areaName || 'Satellite Town');

  // Contact
  const [phone, setPhone] = useState(initialSkill?.phone || currentUser.mobile || '');
  const [whatsapp, setWhatsapp] = useState(initialSkill?.whatsapp || currentUser.mobile || '');
  const [email, setEmail] = useState(initialSkill?.email || currentUser.email || '');
  const [showEmail, setShowEmail] = useState(initialSkill?.showEmail ?? true);

  // Work & Rate
  const [servicesStr, setServicesStr] = useState(initialSkill?.servicesOffered.join(', ') || 'Custom UI Design, WordPress Setup');
  const [startingRate, setStartingRate] = useState<number>(initialSkill?.startingRate || 10000);
  const [availability, setAvailability] = useState<'Full-time' | 'Part-time' | 'Freelance' | 'Hourly'>(
    initialSkill?.availability || 'Freelance'
  );

  // Portfolio & CV
  const [portfolioLink, setPortfolioLink] = useState(initialSkill?.portfolioLinks?.[0]?.url || '');
  const [cvFileName, setCvFileName] = useState<string>(initialSkill?.cvFilePath ? 'current_cv.pdf' : '');
  const [cvPath, setCvPath] = useState<string>(initialSkill?.cvFilePath || '');
  const [error, setError] = useState('');

  if (!isOpen) return null;

  // Dependent dropdowns
  const availableTehsils = tehsils.filter((t) => t.districtId === districtId && t.isActive);
  const availableAreas = areas.filter((a) => a.tehsilId === tehsilId && a.isActive);

  const handleCvUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
        setError('Only PDF documents are allowed for CV upload.');
        return;
      }
      if (file.size > 10 * 1024 * 1024) {
        setError('CV PDF must be under 10MB.');
        return;
      }
      setCvFileName(file.name);
      setCvPath('/uploads/cv_' + Date.now() + '_' + file.name.replace(/[^a-zA-Z0-9._-]/g, '_'));
      setError('');
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!fullName.trim() || !professionalTitle.trim() || !about.trim()) {
      setError('Please provide your Full Name, Professional Title, and About summary.');
      return;
    }
    if (!phone.trim() || !whatsapp.trim()) {
      setError('Please provide direct Call and WhatsApp numbers.');
      return;
    }

    const currentDistrict = districts.find((d) => d.id === districtId);
    const currentTehsil = tehsils.find((t) => t.id === tehsilId);

    const skillsArray = skillsStr.split(',').map((s) => s.trim()).filter(Boolean);
    const servicesArray = servicesStr.split(',').map((s) => s.trim()).filter(Boolean);

    onSaveSkill(
      {
        userId: currentUser.id,
        fullName: fullName.trim(),
        username: currentUser.name.toLowerCase().replace(/[^a-z0-9]/g, '_') + '_' + Date.now().toString().slice(-4),
        profilePhoto: initialSkill?.profilePhoto || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=80',
        professionalTitle: professionalTitle.trim(),
        mainSkill,
        category,
        skills: skillsArray.length > 0 ? skillsArray : [mainSkill],
        experience,
        about: about.trim(),
        divisionId: 1,
        districtId,
        tehsilId,
        areaId: 1,
        divisionName: 'Sargodha Division',
        districtName: currentDistrict?.name || 'Sargodha',
        tehsilName: currentTehsil?.name || 'Sargodha Tehsil',
        areaName: areaName || 'Sargodha City',
        phone: phone.trim(),
        whatsapp: whatsapp.trim(),
        email: email.trim(),
        showEmail,
        portfolioLinks: portfolioLink.trim()
          ? [{ label: 'Online Portfolio', url: portfolioLink.trim() }]
          : [],
        servicesOffered: servicesArray.length > 0 ? servicesArray : ['Professional Consulting'],
        startingRate: Number(startingRate) || 0,
        availability,
        cvFilePath: cvPath,
        status: 'approved', // Active sellers get approved directly
        isFeatured: initialSkill?.isFeatured || false,
        featuredStartAt: initialSkill?.featuredStartAt,
        featuredEndAt: initialSkill?.featuredEndAt,
      },
      initialSkill?.id
    );

    onClose();
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md animate-in fade-in duration-200">
      <div className="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-cyan-100 overflow-hidden flex flex-col max-h-[92vh]">
        {/* Header */}
        <div className="p-5 bg-gradient-to-r from-slate-900 via-cyan-950 to-slate-900 text-white flex items-center justify-between">
          <div className="flex items-center gap-2.5">
            <div className="w-10 h-10 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/25">
              <Briefcase className="w-5 h-5" />
            </div>
            <div>
              <h2 className="text-base sm:text-lg font-black text-white">
                {initialSkill ? 'Edit Digital Skill Profile' : 'Create Digital Skill Profile & Online CV'}
              </h2>
              <p className="text-xs text-cyan-200/80 font-medium">
                Showcase your digital expertise to Sargodha & regional clients
              </p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition-colors"
          >
            <X className="w-4 h-4" />
          </button>
        </div>

        {/* Body Form */}
        <form onSubmit={handleSubmit} className="p-6 overflow-y-auto space-y-5 flex-1">
          {error && (
            <div className="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
              <AlertCircle className="w-4 h-4 text-rose-600 shrink-0" />
              <span>{error}</span>
            </div>
          )}

          {/* Full Name & Title */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-black text-slate-800 mb-1.5 uppercase tracking-wider">
                Full Display Name <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={fullName}
                onChange={(e) => setFullName(e.target.value)}
                placeholder="e.g. Hamza Farooq"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-900 focus:outline-hidden focus:border-cyan-500"
              />
            </div>
            <div>
              <label className="block text-xs font-black text-slate-800 mb-1.5 uppercase tracking-wider">
                Professional Title <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={professionalTitle}
                onChange={(e) => setProfessionalTitle(e.target.value)}
                placeholder="e.g. Senior Graphic & UI/UX Designer"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-900 focus:outline-hidden focus:border-cyan-500"
              />
            </div>
          </div>

          {/* Main Skill & Category */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-black text-slate-800 mb-1.5 uppercase tracking-wider">
                Primary Digital Skill <span className="text-rose-500">*</span>
              </label>
              <select
                value={mainSkill}
                onChange={(e) => setMainSkill(e.target.value)}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-900 focus:outline-hidden focus:border-cyan-500 bg-white"
              >
                <option value="Website Designer">Website Designer</option>
                <option value="Web Developer">Web Developer (PHP / React / Full-Stack)</option>
                <option value="Graphic Designer">Graphic Designer</option>
                <option value="Logo Designer">Logo & Brand Identity Designer</option>
                <option value="Video Editor">Video Editor (Reels & Long-form)</option>
                <option value="Video Animator">Video Animator / Motion Graphics</option>
                <option value="Social Media Manager">Social Media Manager</option>
                <option value="SEO Specialist">SEO Specialist</option>
                <option value="Content Writer">Content & Copywriter</option>
                <option value="Data Entry">Data Entry & Virtual Assistant</option>
                <option value="Mobile App Developer">Mobile App Developer (Flutter / React Native)</option>
                <option value="UI/UX Designer">UI/UX Designer (Figma)</option>
                <option value="Digital Marketer">Digital Marketer (Meta & Google Ads)</option>
                <option value="Photographer">Photographer & Product Shoots</option>
                <option value="Computer Technician">Computer / Laptop Hardware Technician</option>
              </select>
            </div>

            <div>
              <label className="block text-xs font-black text-slate-800 mb-1.5 uppercase tracking-wider">
                Skill Category
              </label>
              <select
                value={category}
                onChange={(e) => setCategory(e.target.value)}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-900 focus:outline-hidden focus:border-cyan-500 bg-white"
              >
                <option value="Design & Creative">Design & Creative</option>
                <option value="Development & IT">Development & IT</option>
                <option value="Video & Animation">Video & Animation</option>
                <option value="Writing & Translation">Writing & Translation</option>
                <option value="Digital Marketing">Digital Marketing</option>
                <option value="Tech Support & Hardware">Tech Support & Hardware</option>
              </select>
            </div>
          </div>

          {/* Skills Tags & Experience */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-black text-slate-800 mb-1.5 uppercase tracking-wider">
                Skills / Tools (comma-separated)
              </label>
              <input
                type="text"
                value={skillsStr}
                onChange={(e) => setSkillsStr(e.target.value)}
                placeholder="Figma, Adobe Photoshop, Tailwind, React"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-900 focus:outline-hidden focus:border-cyan-500"
              />
            </div>

            <div>
              <label className="block text-xs font-black text-slate-800 mb-1.5 uppercase tracking-wider">
                Experience Level
              </label>
              <select
                value={experience}
                onChange={(e) => setExperience(e.target.value)}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-900 focus:outline-hidden focus:border-cyan-500 bg-white"
              >
                <option value="1 Year">1 Year</option>
                <option value="2 Years">2 Years</option>
                <option value="3 Years">3 Years</option>
                <option value="4+ Years">4+ Years</option>
                <option value="6+ Years">6+ Years (Expert)</option>
              </select>
            </div>
          </div>

          {/* About Summary */}
          <div>
            <label className="block text-xs font-black text-slate-800 mb-1.5 uppercase tracking-wider">
              About / Professional Bio <span className="text-rose-500">*</span>
            </label>
            <textarea
              rows={3}
              value={about}
              onChange={(e) => setAbout(e.target.value)}
              placeholder="Introduce your background, achievements, and what makes your work reliable for clients in Sargodha and beyond..."
              required
              className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-medium text-slate-900 focus:outline-hidden focus:border-cyan-500 leading-relaxed"
            />
          </div>

          {/* Location Hierarchy (Division -> District -> Tehsil -> Area) */}
          <div className="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
            <div className="text-xs font-black text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
              <MapPin className="w-3.5 h-3.5 text-cyan-600" /> Location Hierarchy
            </div>
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <label className="block text-[10px] font-bold text-slate-600 uppercase mb-1">District</label>
                <select
                  value={districtId}
                  onChange={(e) => {
                    const dId = Number(e.target.value);
                    setDistrictId(dId);
                    const firstT = tehsils.find((t) => t.districtId === dId);
                    if (firstT) setTehsilId(firstT.id);
                  }}
                  className="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-900 bg-white"
                >
                  {districts.map((d) => (
                    <option key={d.id} value={d.id}>{d.name}</option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-[10px] font-bold text-slate-600 uppercase mb-1">Tehsil</label>
                <select
                  value={tehsilId}
                  onChange={(e) => setTehsilId(Number(e.target.value))}
                  className="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-900 bg-white"
                >
                  {availableTehsils.map((t) => (
                    <option key={t.id} value={t.id}>{t.name}</option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-[10px] font-bold text-slate-600 uppercase mb-1">Local Area / Bazaar</label>
                <input
                  type="text"
                  value={areaName}
                  onChange={(e) => setAreaName(e.target.value)}
                  placeholder="e.g. Satellite Town / Main Mandi"
                  className="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold text-slate-900"
                />
              </div>
            </div>
          </div>

          {/* Contact Details */}
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label className="block text-xs font-black text-slate-800 mb-1.5 uppercase tracking-wider">
                Call Phone <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={phone}
                onChange={(e) => setPhone(e.target.value)}
                placeholder="03127453108"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-900"
              />
            </div>
            <div>
              <label className="block text-xs font-black text-slate-800 mb-1.5 uppercase tracking-wider">
                WhatsApp <span className="text-rose-500">*</span>
              </label>
              <input
                type="text"
                value={whatsapp}
                onChange={(e) => setWhatsapp(e.target.value)}
                placeholder="03127453108"
                required
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-900"
              />
            </div>
            <div>
              <label className="block text-xs font-black text-slate-800 mb-1.5 uppercase tracking-wider">
                Availability
              </label>
              <select
                value={availability}
                onChange={(e) => setAvailability(e.target.value as any)}
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-900 bg-white"
              >
                <option value="Freelance">Freelance</option>
                <option value="Full-time">Full-time</option>
                <option value="Part-time">Part-time</option>
                <option value="Hourly">Hourly</option>
              </select>
            </div>
          </div>

          {/* Services & Starting Rate */}
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div className="sm:col-span-2">
              <label className="block text-xs font-black text-slate-800 mb-1.5 uppercase tracking-wider">
                Services Offered (comma-separated)
              </label>
              <input
                type="text"
                value={servicesStr}
                onChange={(e) => setServicesStr(e.target.value)}
                placeholder="Logo Design, Social Media Posts, Brand Kit"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-900"
              />
            </div>
            <div>
              <label className="block text-xs font-black text-slate-800 mb-1.5 uppercase tracking-wider">
                Starting Rate (PKR)
              </label>
              <input
                type="number"
                value={startingRate}
                onChange={(e) => setStartingRate(Number(e.target.value))}
                placeholder="10000"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-900"
              />
            </div>
          </div>

          {/* Portfolio & CV Upload */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-black text-slate-800 mb-1.5 uppercase tracking-wider">
                Portfolio / GitHub / Behance Link
              </label>
              <input
                type="url"
                value={portfolioLink}
                onChange={(e) => setPortfolioLink(e.target.value)}
                placeholder="https://behance.net/username"
                className="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-900"
              />
            </div>

            <div>
              <label className="block text-xs font-black text-slate-800 mb-1.5 uppercase tracking-wider">
                Upload Digital CV (PDF Only, Max 10MB)
              </label>
              <div className="flex items-center gap-2">
                <label className="flex-1 border-2 border-dashed border-slate-300 hover:border-cyan-500 rounded-xl px-3 py-2 text-center cursor-pointer bg-slate-50 hover:bg-cyan-50/40 transition-colors flex items-center justify-center gap-2">
                  <Upload className="w-4 h-4 text-cyan-600" />
                  <span className="text-xs font-bold text-slate-700">
                    {cvFileName || 'Select PDF file'}
                  </span>
                  <input type="file" accept="application/pdf" onChange={handleCvUpload} className="hidden" />
                </label>
                {cvFileName && (
                  <span className="text-xs font-bold text-emerald-600 flex items-center gap-1">
                    <CheckCircle2 className="w-4 h-4" /> Ready
                  </span>
                )}
              </div>
            </div>
          </div>

          {/* Footer Submit */}
          <div className="pt-3 border-t border-slate-200 flex items-center justify-end gap-3">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-100 transition-all"
            >
              Cancel
            </button>
            <button
              type="submit"
              className="px-6 py-2.5 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-bold text-xs shadow-md shadow-cyan-600/30 transition-all"
            >
              {initialSkill ? 'Update Digital Profile' : 'Publish Digital Skill & CV'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
