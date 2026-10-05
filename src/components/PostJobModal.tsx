import React, { useState, useMemo } from 'react';
import { X, Briefcase, PlusCircle, CheckCircle2, ShieldAlert, MapPin } from 'lucide-react';
import {
  UserProfile,
  JobPost,
  JobPostType,
  City,
  SiteSettings,
  District,
  Tehsil,
  AreaLocation,
} from '../types';
import { INITIAL_DISTRICTS, INITIAL_TEHSILS, INITIAL_AREAS } from '../data/locationData';

interface PostJobModalProps {
  currentUser: UserProfile;
  isOpen: boolean;
  onClose: () => void;
  onSubmitJob: (jobData: Omit<JobPost, 'id' | 'views' | 'createdAt'>) => void;
  onOpenActivation: () => void;
  settings?: SiteSettings;
  districts?: District[];
  tehsils?: Tehsil[];
  areas?: AreaLocation[];
}

export const PostJobModal: React.FC<PostJobModalProps> = ({
  currentUser,
  isOpen,
  onClose,
  onSubmitJob,
  onOpenActivation,
  settings,
  districts = INITIAL_DISTRICTS,
  tehsils = INITIAL_TEHSILS,
  areas = INITIAL_AREAS,
}) => {
  const [postType, setPostType] = useState<JobPostType>('need_worker');
  const [title, setTitle] = useState('');
  const [category, setCategory] = useState('General Staff');
  const [skills, setSkills] = useState('');
  const [experience, setExperience] = useState('');
  const [workingHours, setWorkingHours] = useState('');
  const [salaryOrPayment, setSalaryOrPayment] = useState('');

  // Location Hierarchy State
  const initialDistrictId = useMemo(() => {
    const match = districts.find((d) => d.name.toLowerCase() === currentUser.city.toLowerCase());
    return match ? match.id : 1;
  }, [districts, currentUser.city]);

  const [districtId, setDistrictId] = useState<number>(initialDistrictId);

  const availableTehsils = useMemo(() => {
    return tehsils.filter((t) => t.districtId === districtId && t.isActive);
  }, [tehsils, districtId]);

  const [tehsilId, setTehsilId] = useState<number>(() => {
    return availableTehsils[0]?.id || 1;
  });

  const availableAreas = useMemo(() => {
    return areas.filter((a) => a.tehsilId === tehsilId && a.isActive);
  }, [areas, tehsilId]);

  const [area, setArea] = useState(currentUser.area || '');
  const [exactLocation, setExactLocation] = useState('');
  const [phone, setPhone] = useState(currentUser.mobile || '');
  const [whatsapp, setWhatsapp] = useState(currentUser.mobile || '');
  const [description, setDescription] = useState('');
  const [error, setError] = useState('');

  const handleDistrictChange = (newDistId: number) => {
    setDistrictId(newDistId);
    const related = tehsils.filter((t) => t.districtId === newDistId && t.isActive);
    if (related.length > 0) {
      setTehsilId(related[0].id);
    }
  };

  if (!isOpen) return null;

  if (currentUser.activationStatus !== 'active') {
    const feeFormatted = `Rs. ${(settings?.activationFee || 1000).toLocaleString()}`;
    return (
      <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-md">
        <div className="relative w-full max-w-md bg-white border border-amber-200 rounded-3xl p-6 text-center shadow-[0_20px_50px_rgba(245,158,11,0.2)]">
          <button onClick={onClose} className="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-800">
            <X className="w-5 h-5" />
          </button>
          <div className="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-4 border border-amber-300 shadow-[0_0_12px_rgba(245,158,11,0.2)]">
            <ShieldAlert className="w-8 h-8" />
          </div>
          <h3 className="text-xl font-bold text-slate-900 mb-2">Lifetime Activation Required</h3>
          <p className="text-slate-600 text-xs mb-6 font-medium">
            To post job opportunities or seek work across Sargodha Division, activate your lifetime account for <strong className="text-cyan-700 font-bold">{feeFormatted} once</strong>.
          </p>
          <button
            onClick={() => {
              onClose();
              onOpenActivation();
            }}
            className="w-full py-3 rounded-xl text-xs font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-lg shadow-cyan-500/25 transition-all"
          >
            Activate Account ({feeFormatted} Once)
          </button>
        </div>
      </div>
    );
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!title.trim() || !skills.trim() || !description.trim()) {
      setError('Please fill in title, skills, and description.');
      return;
    }

    const selectedDist = districts.find((d) => d.id === districtId);
    const selectedTeh = tehsils.find((t) => t.id === tehsilId);
    const distName = selectedDist?.name || 'Sargodha';
    const tehName = selectedTeh?.name || 'Sargodha';

    onSubmitJob({
      userId: currentUser.id,
      posterName: currentUser.name,
      postType,
      title: title.trim(),
      category,
      skills: skills.trim(),
      experience: experience.trim() || undefined,
      workingHours: workingHours.trim() || undefined,
      salaryOrPayment: salaryOrPayment.trim() || undefined,
      divisionId: 1,
      districtId,
      tehsilId,
      districtName: distName,
      tehsilName: tehName,
      city: distName as City,
      area: area.trim() || tehName,
      description: description.trim(),
      phone: phone.trim(),
      whatsapp: whatsapp.trim(),
      status: 'published',
    });

    onClose();
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-md overflow-y-auto">
      <div className="relative w-full max-w-2xl bg-white border border-blue-200 rounded-3xl p-6 md:p-8 shadow-[0_20px_60px_-15px_rgba(37,99,235,0.25)] my-8 max-h-[90vh] overflow-y-auto">
        <button
          onClick={onClose}
          className="absolute top-5 right-5 p-2 rounded-xl text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-all"
        >
          <X className="w-5 h-5" />
        </button>

        <div className="mb-6">
          <h2 className="text-xl font-bold text-slate-900 flex items-center gap-2">
            <Briefcase className="w-5 h-5 text-blue-600" /> Post Job / Work Requirement
          </h2>
          <p className="text-slate-500 text-xs font-medium">
            Directly published to the Sargodha, Shaheenabad & Sillanwali community.
          </p>
        </div>

        {error && (
          <div className="p-3 rounded-xl bg-rose-50 border border-rose-300 text-rose-700 text-xs mb-4 font-semibold">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-4 text-xs">
          {/* Post Type Selector */}
          <div>
            <label className="block font-semibold text-slate-800 mb-1.5">What are you posting? *</label>
            <div className="grid grid-cols-2 gap-3">
              <button
                type="button"
                onClick={() => setPostType('need_worker')}
                className={`py-3 px-4 rounded-2xl font-bold border transition-all text-left flex items-center gap-2.5 ${
                  postType === 'need_worker'
                    ? 'bg-blue-50 border-blue-400 text-blue-900 shadow-md shadow-blue-500/10'
                    : 'bg-slate-50 border-slate-200 text-slate-600 hover:border-slate-300'
                }`}
              >
                <span className="text-xl">🏢</span>
                <div>
                  <div className="font-bold">I Need a Worker</div>
                  <div className="text-[10px] text-slate-500 font-medium">Hiring for business/shop/farm</div>
                </div>
              </button>

              <button
                type="button"
                onClick={() => setPostType('need_job')}
                className={`py-3 px-4 rounded-2xl font-bold border transition-all text-left flex items-center gap-2.5 ${
                  postType === 'need_job'
                    ? 'bg-cyan-50 border-cyan-400 text-cyan-900 shadow-md shadow-cyan-500/10'
                    : 'bg-slate-50 border-slate-200 text-slate-600 hover:border-slate-300'
                }`}
              >
                <span className="text-xl">👤</span>
                <div>
                  <div className="font-bold">I Need a Job</div>
                  <div className="text-[10px] text-slate-500 font-medium">Worker offering skills/services</div>
                </div>
              </button>
            </div>
          </div>

          {/* Title */}
          <div>
            <label className="block font-semibold text-slate-800 mb-1">Job Title / Work Headline *</label>
            <input
              type="text"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              placeholder={
                postType === 'need_worker'
                  ? 'e.g. Dairy farm helper needed, Tractor driver required, Shop sales boy...'
                  : 'e.g. Master tractor driver with 5 yrs exp, Mobile repair technician available...'
              }
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-blue-500 focus:bg-white font-medium"
              required
            />
          </div>

          {/* Category & Skills */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
              <label className="block font-semibold text-slate-800 mb-1">Industry / Category *</label>
              <select
                value={category}
                onChange={(e) => setCategory(e.target.value)}
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-blue-500 focus:bg-white font-medium"
              >
                <option value="Livestock & Agriculture">Livestock & Agriculture</option>
                <option value="Driving & Machinery">Driving & Machinery</option>
                <option value="Sales & Retail">Sales & Retail</option>
                <option value="Mobile & Electronics Repair">Mobile & Electronics Repair</option>
                <option value="Teaching & Education">Teaching & Education</option>
                <option value="Solar & Electrical">Solar & Electrical</option>
                <option value="General Labor / Helper">General Labor / Helper</option>
                <option value="Other Skills">Other Skills</option>
              </select>
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">Required or Offered Skills *</label>
              <input
                type="text"
                value={skills}
                onChange={(e) => setSkills(e.target.value)}
                placeholder="e.g. Milking, Fiat 480, Soldering, Cash Handling..."
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-blue-500 focus:bg-white font-medium"
                required
              />
            </div>
          </div>

          {/* Salary / Payment & Hours */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
              <label className="block font-semibold text-slate-800 mb-1">Salary / Compensation (Optional)</label>
              <input
                type="text"
                value={salaryOrPayment}
                onChange={(e) => setSalaryOrPayment(e.target.value)}
                placeholder="e.g. Rs. 35,000 / month, Rs. 1,500 / day, Negotiable"
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-blue-500 focus:bg-white font-medium"
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">Working Hours / Timing (Optional)</label>
              <input
                type="text"
                value={workingHours}
                onChange={(e) => setWorkingHours(e.target.value)}
                placeholder="e.g. Full-time, 9am to 6pm, Night shift"
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-blue-500 focus:bg-white font-medium"
              />
            </div>
          </div>

          {/* Structured Location Hierarchy: Division -> District -> Tehsil -> Area */}
          <div className="p-4 rounded-2xl bg-blue-50/40 border border-blue-200/80 space-y-3">
            <div className="flex items-center justify-between">
              <span className="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                <MapPin className="w-4 h-4 text-blue-600" />
                <span>Job Location (District → Tehsil → Area)</span>
              </span>
              <span className="text-[10px] font-bold text-blue-800 bg-blue-100/80 px-2 py-0.5 rounded-full border border-blue-200">
                Sargodha Division
              </span>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
              {/* Step 1: District */}
              <div>
                <label className="block font-semibold text-slate-800 mb-1 text-[11px]">
                  Step 1: Select District *
                </label>
                <select
                  value={districtId}
                  onChange={(e) => handleDistrictChange(Number(e.target.value))}
                  className="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-blue-500 font-medium"
                >
                  {districts.filter((d) => d.isActive).map((d) => (
                    <option key={d.id} value={d.id}>
                      {d.name} District
                    </option>
                  ))}
                </select>
              </div>

              {/* Step 2: Tehsil (Dependent on District) */}
              <div>
                <label className="block font-semibold text-slate-800 mb-1 text-[11px]">
                  Step 2: Select Tehsil *
                </label>
                <select
                  value={tehsilId}
                  onChange={(e) => setTehsilId(Number(e.target.value))}
                  className="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-blue-500 font-medium"
                >
                  {availableTehsils.map((t) => (
                    <option key={t.id} value={t.id}>
                      {t.name} Tehsil
                    </option>
                  ))}
                </select>
              </div>
            </div>

            {/* Step 3: Area / Town / Village / Locality */}
            <div>
              <label className="block font-semibold text-slate-800 mb-1 text-[11px]">
                Step 3: Area / Town / Village / Locality *
              </label>

              {availableAreas.length > 0 && (
                <div className="flex flex-wrap gap-1.5 mb-2">
                  {availableAreas.map((a) => (
                    <button
                      key={a.id}
                      type="button"
                      onClick={() => setArea(a.name)}
                      className={`text-[10px] px-2 py-0.5 rounded-lg border transition-all ${
                        area === a.name
                          ? 'bg-blue-600 text-white border-blue-600 font-bold'
                          : 'bg-white text-slate-600 border-slate-200 hover:border-blue-400'
                      }`}
                    >
                      {a.name}
                    </button>
                  ))}
                </div>
              )}

              <input
                type="text"
                value={area}
                onChange={(e) => setArea(e.target.value)}
                placeholder="e.g. Shaheenabad, Satellite Town, Mandi Bazaar, Canal Road..."
                className="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-blue-500 font-medium"
                required
              />
            </div>

            {/* Step 4: Optional exact workplace address / shop / mill */}
            <div>
              <label className="block font-semibold text-slate-600 mb-1 text-[11px]">
                Workplace Address / Landmark (Optional)
              </label>
              <input
                type="text"
                value={exactLocation}
                onChange={(e) => setExactLocation(e.target.value)}
                placeholder="e.g. Near Trust Plaza, Shop #5, Industrial Estate..."
                className="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-slate-800 text-[11px] focus:outline-none focus:border-blue-500"
              />
            </div>
          </div>

          {/* Contact Details */}
          <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
              <label className="block font-semibold text-slate-800 mb-1">Calling Phone Number *</label>
              <input
                type="text"
                value={phone}
                onChange={(e) => setPhone(e.target.value)}
                placeholder="03001234567"
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-blue-500 focus:bg-white font-medium"
                required
              />
            </div>

            <div>
              <label className="block font-semibold text-slate-800 mb-1">WhatsApp Number *</label>
              <input
                type="text"
                value={whatsapp}
                onChange={(e) => setWhatsapp(e.target.value)}
                placeholder="03001234567"
                className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-blue-500 focus:bg-white font-medium"
                required
              />
            </div>
          </div>

          {/* Description */}
          <div>
            <label className="block font-semibold text-slate-800 mb-1">Job Description & Requirements *</label>
            <textarea
              rows={4}
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              placeholder="Detail the daily responsibilities, perks, accommodation/food status, or experience..."
              className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-blue-500 focus:bg-white font-medium"
              required
            ></textarea>
          </div>

          {/* Submit Actions */}
          <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 rounded-xl font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-all"
            >
              Cancel
            </button>
            <button
              type="submit"
              className="px-6 py-2.5 rounded-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white shadow-lg shadow-blue-600/25 flex items-center gap-2 hover:shadow-[0_0_15px_rgba(37,99,235,0.4)] transition-all"
            >
              <CheckCircle2 className="w-4 h-4" />
              Publish Job Post Directly
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
