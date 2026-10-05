import React, { useState } from 'react';
import { X, Megaphone, Sparkles, CheckCircle2, AlertCircle, Upload, Shield, ArrowRight, ArrowLeft, Send, Check } from 'lucide-react';
import { AdvertisingPackage, UserProfile, SiteSettings, Advertisement, AdvertisingPayment } from '../types';

interface CreateAdModalProps {
  isOpen: boolean;
  onClose: () => void;
  currentUser: UserProfile;
  packages: AdvertisingPackage[];
  settings?: SiteSettings;
  preselectedPackageId?: number;
  onSubmitAd: (
    adData: Omit<Advertisement, 'id' | 'status' | 'amountPaid' | 'durationDays' | 'createdAt' | 'updatedAt'>,
    packageId: number
  ) => void;
}

export const CreateAdModal: React.FC<CreateAdModalProps> = ({
  isOpen,
  onClose,
  currentUser,
  packages,
  settings,
  preselectedPackageId,
  onSubmitAd,
}) => {
  const activePackages = packages.filter((p) => p.isActive);
  const [selectedPkgId, setSelectedPkgId] = useState<number>(
    preselectedPackageId || activePackages[0]?.id || 1
  );

  const [step, setStep] = useState<1 | 2 | 3 | 4>(1);

  // Form Fields
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [phone, setPhone] = useState(currentUser.mobile || '');
  const [whatsapp, setWhatsapp] = useState(currentUser.mobile || '');
  const [location, setLocation] = useState(`${currentUser.city} (${currentUser.area})`);
  const [targetUrl, setTargetUrl] = useState('');
  const [bannerPreview, setBannerPreview] = useState<string>('');
  
  // Payment Proof Fields
  const [transactionReference, setTransactionReference] = useState('');
  const [paymentScreenshot, setPaymentScreenshot] = useState<string>('');
  const [error, setError] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  if (!isOpen) return null;

  const currentPkg = activePackages.find((p) => p.id === selectedPkgId) || activePackages[0];

  const handleBannerUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      if (file.size > 5 * 1024 * 1024) {
        setError('Banner image size must be under 5MB.');
        return;
      }
      const reader = new FileReader();
      reader.onloadend = () => {
        setBannerPreview(reader.result as string);
        setError('');
      };
      reader.readAsDataURL(file);
    }
  };

  const handleScreenshotUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      if (file.size > 5 * 1024 * 1024) {
        setError('Payment receipt size must be under 5MB.');
        return;
      }
      const reader = new FileReader();
      reader.onloadend = () => {
        setPaymentScreenshot(reader.result as string);
        setError('');
      };
      reader.readAsDataURL(file);
    }
  };

  const handleProceedToStep2 = () => {
    if (!currentPkg) {
      setError('Please select an active advertising package.');
      return;
    }
    setError('');
    setStep(2);
  };

  const handleProceedToStep3 = () => {
    if (!title.trim()) {
      setError('Business / Advertisement title is required.');
      return;
    }
    if (!description.trim()) {
      setError('Advertisement description is required.');
      return;
    }
    if (!phone.trim()) {
      setError('Contact phone number is required.');
      return;
    }
    if (!whatsapp.trim()) {
      setError('WhatsApp number is required.');
      return;
    }
    if (!bannerPreview) {
      setError('Please upload the advertisement banner or product photo.');
      return;
    }
    setError('');
    setStep(3);
  };

  const handleProceedToStep4 = () => {
    if (!transactionReference.trim()) {
      setError('Transaction ID / Reference Number is required.');
      return;
    }
    if (!paymentScreenshot) {
      setError('Payment screenshot proof is required.');
      return;
    }
    setError('');
    setStep(4);
  };

  const handleFinalSubmit = () => {
    setIsSubmitting(true);
    setError('');

    setTimeout(() => {
      onSubmitAd(
        {
          userId: currentUser.id,
          userName: currentUser.name,
          userPhone: currentUser.mobile,
          userEmail: currentUser.email,
          packageId: currentPkg.id,
          packageName: currentPkg.name,
          adType: currentPkg.adType,
          title: title.trim(),
          description: description.trim(),
          imageUrl: bannerPreview,
          targetUrl: targetUrl.trim(),
          phone: phone.trim(),
          whatsapp: whatsapp.trim(),
          location: location.trim(),
          placements: currentPkg.placements,
          transactionReference: transactionReference.trim(),
          paymentScreenshot,
          telegramEnabled: currentPkg.telegramEnabled,
          telegramStatus: currentPkg.telegramEnabled ? 'pending' : 'not_applicable',
        },
        currentPkg.id
      );
      setIsSubmitting(false);
      onClose();
    }, 600);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md animate-in fade-in duration-200">
      <div className="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-cyan-100 overflow-hidden flex flex-col max-h-[92vh]">
        {/* Header */}
        <div className="p-5 bg-gradient-to-r from-slate-950 via-slate-900 to-amber-950 text-white flex items-center justify-between border-b border-amber-500/20">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 flex items-center justify-center text-slate-950 font-black shadow-lg shadow-amber-500/30">
              <Megaphone className="w-5 h-5" />
            </div>
            <div>
              <h2 className="text-base sm:text-lg font-black tracking-tight flex items-center gap-2">
                Book Advertisement Campaign
                <span className="px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 text-[10px] font-black uppercase">
                  Step {step} of 4
                </span>
              </h2>
              <p className="text-[11px] text-amber-200/90 font-medium">
                {step === 1 && 'Select from Admin-Configured Advertising Packages'}
                {step === 2 && 'Enter Your Advertisement Artwork & Contact Details'}
                {step === 3 && 'Submit Verification Payment Proof'}
                {step === 4 && 'Confirm & Submit for Admin Approval'}
              </p>
            </div>
          </div>

          <button
            onClick={onClose}
            className="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition-colors"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Progress Tracker */}
        <div className="grid grid-cols-4 bg-slate-100 text-[10px] font-bold border-b border-slate-200 text-center">
          <div className={`py-2 ${step >= 1 ? 'bg-amber-100 text-amber-900 font-extrabold border-b-2 border-amber-500' : 'text-slate-500'}`}>
            1. Package
          </div>
          <div className={`py-2 ${step >= 2 ? 'bg-amber-100 text-amber-900 font-extrabold border-b-2 border-amber-500' : 'text-slate-500'}`}>
            2. Details
          </div>
          <div className={`py-2 ${step >= 3 ? 'bg-amber-100 text-amber-900 font-extrabold border-b-2 border-amber-500' : 'text-slate-500'}`}>
            3. Payment
          </div>
          <div className={`py-2 ${step >= 4 ? 'bg-amber-100 text-amber-900 font-extrabold border-b-2 border-amber-500' : 'text-slate-500'}`}>
            4. Review
          </div>
        </div>

        {/* Modal Body */}
        <div className="p-6 overflow-y-auto space-y-5 flex-1">
          {error && (
            <div className="p-3 rounded-2xl bg-rose-50 border border-rose-300 text-rose-800 text-xs font-bold flex items-center gap-2">
              <AlertCircle className="w-4 h-4 shrink-0 text-rose-600" />
              <span>{error}</span>
            </div>
          )}

          {/* STEP 1: SELECT PACKAGE */}
          {step === 1 && (
            <div className="space-y-4">
              <div className="text-xs text-slate-600 font-medium">
                Choose the promotional package that fits your business target. All prices and durations are officially managed by SargodhaMart Admin:
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                {activePackages.map((pkg) => (
                  <div
                    key={pkg.id}
                    onClick={() => {
                      setSelectedPkgId(pkg.id);
                      setError('');
                    }}
                    className={`p-4 rounded-2xl border-2 transition-all cursor-pointer flex flex-col justify-between relative ${
                      selectedPkgId === pkg.id
                        ? 'border-amber-500 bg-amber-50/50 shadow-md shadow-amber-500/10'
                        : 'border-slate-200 hover:border-slate-300 bg-white'
                    }`}
                  >
                    {pkg.recommended && (
                      <span className="absolute -top-2.5 right-3 px-2 py-0.5 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 text-[9px] font-black uppercase shadow-xs flex items-center gap-0.5">
                        <Sparkles className="w-2.5 h-2.5 fill-slate-950" /> RECOMMENDED
                      </span>
                    )}

                    <div>
                      <div className="flex items-center justify-between gap-2 mb-1">
                        <h4 className="text-sm font-black text-slate-900">{pkg.name}</h4>
                        {selectedPkgId === pkg.id && (
                          <div className="w-5 h-5 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center shrink-0">
                            <Check className="w-3.5 h-3.5 stroke-[3]" />
                          </div>
                        )}
                      </div>

                      <p className="text-[11px] text-slate-500 line-clamp-2 mb-3 leading-relaxed">
                        {pkg.description}
                      </p>

                      <div className="flex flex-wrap gap-1 mb-3">
                        {pkg.placements.map((plc, idx) => (
                          <span
                            key={idx}
                            className="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-bold"
                          >
                            {plc}
                          </span>
                        ))}
                        {pkg.telegramEnabled && (
                          <span className="px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 text-[10px] font-extrabold flex items-center gap-0.5">
                            📲 Telegram
                          </span>
                        )}
                      </div>
                    </div>

                    <div className="pt-2 border-t border-slate-100 flex items-baseline justify-between">
                      <span className="text-xs text-slate-500 font-bold">{pkg.durationDays} Days</span>
                      <span className="text-base font-black text-cyan-800">Rs. {pkg.price.toLocaleString()}</span>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* STEP 2: ADVERTISEMENT DETAILS */}
          {step === 2 && currentPkg && (
            <div className="space-y-4">
              <div className="p-3 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-between text-xs">
                <div>
                  <span className="text-slate-500">Selected Package: </span>
                  <strong className="text-slate-950 font-bold">{currentPkg.name}</strong>
                </div>
                <div className="text-amber-900 font-black">
                  Rs. {currentPkg.price.toLocaleString()} / {currentPkg.durationDays} Days
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-800 mb-1">
                  Business / Ad Campaign Title <span className="text-rose-500">*</span>
                </label>
                <input
                  type="text"
                  value={title}
                  onChange={(e) => setTitle(e.target.value)}
                  placeholder="e.g. Al-Madina Dental Clinic / Best Solar Solutions Sargodha"
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-hidden focus:border-amber-500 font-medium"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-800 mb-1">
                  Campaign Description / Services Offered <span className="text-rose-500">*</span>
                </label>
                <textarea
                  rows={3}
                  value={description}
                  onChange={(e) => setDescription(e.target.value)}
                  placeholder="Describe your special offer, products, discounts, or guarantees..."
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-hidden focus:border-amber-500 font-medium"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-800 mb-1">
                  Upload Ad Banner Artwork / Flyer <span className="text-rose-500">*</span>
                </label>
                <div className="flex items-center gap-4">
                  {bannerPreview ? (
                    <div className="relative w-28 h-20 rounded-xl overflow-hidden border border-amber-300">
                      <img src={bannerPreview} alt="Preview" className="w-full h-full object-cover" />
                      <button
                        type="button"
                        onClick={() => setBannerPreview('')}
                        className="absolute top-1 right-1 p-1 bg-slate-950/70 text-white rounded-md text-[10px]"
                      >
                        Change
                      </button>
                    </div>
                  ) : (
                    <label className="flex-1 border-2 border-dashed border-amber-300 rounded-2xl p-4 flex flex-col items-center justify-center cursor-pointer hover:bg-amber-50/50 transition-colors">
                      <Upload className="w-6 h-6 text-amber-600 mb-1" />
                      <span className="text-xs font-bold text-slate-800">Upload Banner Image (JPG, PNG)</span>
                      <span className="text-[10px] text-slate-400">Max size 5MB</span>
                      <input type="file" accept="image/*" onChange={handleBannerUpload} className="hidden" />
                    </label>
                  )}
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-bold text-slate-800 mb-1">
                    Phone for Calls <span className="text-rose-500">*</span>
                  </label>
                  <input
                    type="text"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    placeholder="03001234567"
                    className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-hidden focus:border-amber-500 font-medium"
                  />
                </div>

                <div>
                  <label className="block text-xs font-bold text-slate-800 mb-1">
                    WhatsApp Number <span className="text-rose-500">*</span>
                  </label>
                  <input
                    type="text"
                    value={whatsapp}
                    onChange={(e) => setWhatsapp(e.target.value)}
                    placeholder="03001234567"
                    className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-hidden focus:border-amber-500 font-medium"
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-bold text-slate-800 mb-1">
                    Business Location / Address <span className="text-rose-500">*</span>
                  </label>
                  <input
                    type="text"
                    value={location}
                    onChange={(e) => setLocation(e.target.value)}
                    placeholder="e.g. University Road, Sargodha"
                    className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-hidden focus:border-amber-500 font-medium"
                  />
                </div>

                <div>
                  <label className="block text-xs font-bold text-slate-800 mb-1">
                    Website or Social Link (Optional)
                  </label>
                  <input
                    type="text"
                    value={targetUrl}
                    onChange={(e) => setTargetUrl(e.target.value)}
                    placeholder="https://facebook.com/mybusiness"
                    className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-hidden focus:border-amber-500 font-medium"
                  />
                </div>
              </div>
            </div>
          )}

          {/* STEP 3: PAYMENT SUBMISSION */}
          {step === 3 && currentPkg && (
            <div className="space-y-4">
              <div className="rounded-2xl bg-slate-900 text-white p-4 space-y-3">
                <div className="flex items-center justify-between border-b border-slate-800 pb-2">
                  <span className="text-xs text-slate-400">Total Campaign Fee:</span>
                  <span className="text-lg font-black text-amber-400">Rs. {currentPkg.price.toLocaleString()}</span>
                </div>
                <div className="text-[11px] text-slate-300 space-y-1">
                  <div>
                    <strong>EasyPaisa: </strong> {settings?.easyPaisaNumber || '0312-7453108'} ({settings?.paymentAccountTitle || 'Muhammad Akram Tayyab'})
                  </div>
                  <div>
                    <strong>JazzCash: </strong> {settings?.jazzCashNumber || '0300-1234567'} ({settings?.paymentAccountTitle || 'Muhammad Akram Tayyab'})
                  </div>
                  <div>
                    <strong>Bank Details: </strong> {settings?.bankDetails || 'Meezan Bank Ltd | Sargodha Branch'}
                  </div>
                </div>
              </div>

              <div className="text-xs text-slate-600 bg-amber-50 border border-amber-200 p-3 rounded-2xl">
                Please transfer exactly <strong className="text-amber-900 font-bold">Rs. {currentPkg.price.toLocaleString()}</strong> to the official accounts above and enter the transaction reference with receipt screenshot below:
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-800 mb-1">
                  Transaction Reference ID <span className="text-rose-500">*</span>
                </label>
                <input
                  type="text"
                  value={transactionReference}
                  onChange={(e) => setTransactionReference(e.target.value)}
                  placeholder="e.g. TRX-98412847 or TID 992810"
                  className="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:outline-hidden focus:border-amber-500 font-medium"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-800 mb-1">
                  Upload Payment Screenshot Receipt <span className="text-rose-500">*</span>
                </label>
                {paymentScreenshot ? (
                  <div className="relative w-36 h-28 rounded-xl overflow-hidden border border-emerald-400 shadow-md">
                    <img src={paymentScreenshot} alt="Payment Receipt" className="w-full h-full object-cover" />
                    <button
                      type="button"
                      onClick={() => setPaymentScreenshot('')}
                      className="absolute top-1 right-1 p-1 bg-slate-950/70 text-white rounded-md text-[10px]"
                    >
                      Change
                    </button>
                  </div>
                ) : (
                  <label className="border-2 border-dashed border-emerald-300 rounded-2xl p-4 flex flex-col items-center justify-center cursor-pointer hover:bg-emerald-50/40 transition-colors">
                    <Upload className="w-6 h-6 text-emerald-600 mb-1" />
                    <span className="text-xs font-bold text-slate-800">Upload Transaction Screenshot</span>
                    <span className="text-[10px] text-slate-400">Max size 5MB</span>
                    <input type="file" accept="image/*" onChange={handleScreenshotUpload} className="hidden" />
                  </label>
                )}
              </div>
            </div>
          )}

          {/* STEP 4: REVIEW & CONFIRM */}
          {step === 4 && currentPkg && (
            <div className="space-y-4">
              <div className="p-4 rounded-2xl bg-amber-50 border border-amber-300 text-xs space-y-2">
                <div className="flex items-center justify-between font-black text-slate-950">
                  <span>{currentPkg.name}</span>
                  <span className="text-amber-800">Rs. {currentPkg.price.toLocaleString()} ({currentPkg.durationDays} Days)</span>
                </div>
                <div className="text-slate-600">
                  <strong>Title: </strong> {title}
                </div>
                <div className="text-slate-600">
                  <strong>Contact: </strong> {phone} • WhatsApp: {whatsapp}
                </div>
                <div className="text-slate-600">
                  <strong>Location: </strong> {location}
                </div>
                <div className="text-slate-600">
                  <strong>Transaction ID: </strong> {transactionReference}
                </div>
                <div className="text-slate-600 flex items-center gap-1">
                  <strong>Placements: </strong> {currentPkg.placements.join(', ')}
                </div>
                {currentPkg.telegramEnabled && (
                  <div className="text-blue-800 font-bold flex items-center gap-1">
                    <span>📲 Includes official Telegram broadcast upon Admin verification</span>
                  </div>
                )}
              </div>

              <div className="p-3 rounded-2xl bg-slate-100 text-slate-600 text-[11px] leading-relaxed flex items-center gap-2">
                <Shield className="w-4 h-4 text-cyan-600 shrink-0" />
                <span>
                  Your advertisement request will be submitted in <strong>PENDING</strong> status. Once our Admin team verifies your payment, your campaign will automatically become <strong>ACTIVE</strong> and start displaying.
                </span>
              </div>
            </div>
          )}
        </div>

        {/* Modal Footer Controls */}
        <div className="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-3">
          {step > 1 ? (
            <button
              type="button"
              onClick={() => setStep((prev) => (prev - 1) as any)}
              className="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-200/60 flex items-center gap-1 transition-all"
            >
              <ArrowLeft className="w-4 h-4" /> Back
            </button>
          ) : (
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-200/60"
            >
              Cancel
            </button>
          )}

          {step === 1 && (
            <button
              type="button"
              onClick={handleProceedToStep2}
              className="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs flex items-center gap-1.5 shadow-md shadow-amber-500/25 transition-all"
            >
              Continue to Details <ArrowRight className="w-4 h-4" />
            </button>
          )}

          {step === 2 && (
            <button
              type="button"
              onClick={handleProceedToStep3}
              className="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs flex items-center gap-1.5 shadow-md shadow-amber-500/25 transition-all"
            >
              Proceed to Payment <ArrowRight className="w-4 h-4" />
            </button>
          )}

          {step === 3 && (
            <button
              type="button"
              onClick={handleProceedToStep4}
              className="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs flex items-center gap-1.5 shadow-md shadow-amber-500/25 transition-all"
            >
              Review Campaign <ArrowRight className="w-4 h-4" />
            </button>
          )}

          {step === 4 && (
            <button
              type="button"
              disabled={isSubmitting}
              onClick={handleFinalSubmit}
              className="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-xs flex items-center gap-2 shadow-lg shadow-emerald-600/30 transition-all hover:scale-102 disabled:opacity-50"
            >
              <Send className="w-4 h-4" />
              <span>{isSubmitting ? 'Submitting...' : 'Submit Advertisement (PENDING)'}</span>
            </button>
          )}
        </div>
      </div>
    </div>
  );
};
