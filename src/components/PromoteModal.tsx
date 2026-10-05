import React, { useState } from 'react';
import { X, Sparkles, CheckCircle2, AlertCircle, Shield, ArrowRight, ArrowLeft, Upload, Check, Zap, Megaphone } from 'lucide-react';
import { Listing, DigitalSkillProfile, UserProfile, SiteSettings, PromotionPayment } from '../types';

interface PromoteModalProps {
  isOpen: boolean;
  onClose: () => void;
  currentUser: UserProfile;
  listings: Listing[];
  skills: DigitalSkillProfile[];
  settings?: SiteSettings;
  preselectedType?: 'PRODUCT' | 'SKILL';
  preselectedId?: number;
  onSubmitPromotion: (paymentData: Omit<PromotionPayment, 'id' | 'createdAt'>) => void;
}

export const PromoteModal: React.FC<PromoteModalProps> = ({
  isOpen,
  onClose,
  currentUser,
  listings,
  skills,
  settings,
  preselectedType = 'PRODUCT',
  preselectedId,
  onSubmitPromotion,
}) => {
  const [step, setStep] = useState<1 | 2 | 3 | 4>(1);
  const [promoType, setPromoType] = useState<'PRODUCT' | 'SKILL'>(preselectedType);
  const [selectedEntityId, setSelectedEntityId] = useState<number>(preselectedId || 0);

  const [transactionRef, setTransactionRef] = useState('');
  const [screenshotPreview, setScreenshotPreview] = useState<string>('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState('');

  if (!isOpen) return null;

  // Filter ONLY items owned by current logged-in user that are approved/published
  const myListings = listings.filter((l) => l.userId === currentUser.id && l.status === 'published');
  const mySkills = skills.filter((s) => s.userId === currentUser.id && s.status === 'approved');

  // Pricing from settings
  const productPrice = settings?.promoProductPrice || 1000;
  const productDuration = settings?.promoProductDuration || 15;
  const skillPrice = settings?.promoSkillPrice || 1000;
  const skillDuration = settings?.promoSkillDuration || 15;

  const currentPrice = promoType === 'PRODUCT' ? productPrice : skillPrice;
  const currentDuration = promoType === 'PRODUCT' ? productDuration : skillDuration;
  const currentTitle =
    promoType === 'PRODUCT'
      ? settings?.promoProductTitle || 'Featured Product Promotion'
      : settings?.promoSkillTitle || 'Featured Digital Skill Profile';

  // Selected item
  const selectedListing = promoType === 'PRODUCT' ? myListings.find((l) => l.id === selectedEntityId) : null;
  const selectedSkill = promoType === 'SKILL' ? mySkills.find((s) => s.id === selectedEntityId) : null;
  const selectedTitle = selectedListing?.title || selectedSkill?.professionalTitle || '';

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      if (file.size > 5 * 1024 * 1024) {
        setError('Screenshot image must be under 5MB.');
        return;
      }
      const reader = new FileReader();
      reader.onloadend = () => {
        setScreenshotPreview(reader.result as string);
        setError('');
      };
      reader.readAsDataURL(file);
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedEntityId) {
      setError('Please select an approved product or digital skill to promote.');
      return;
    }
    if (!transactionRef.trim()) {
      setError('Please provide the valid transaction ID or reference number.');
      return;
    }
    if (!screenshotPreview) {
      setError('Please upload the payment receipt screenshot.');
      return;
    }

    setIsSubmitting(true);
    setError('');

    setTimeout(() => {
      onSubmitPromotion({
        userId: currentUser.id,
        userName: currentUser.name,
        userPhone: currentUser.mobile,
        promotionType: promoType,
        entityType: promoType === 'PRODUCT' ? 'PRODUCT' : 'SKILL_PROFILE',
        entityId: selectedEntityId,
        entityTitle: selectedTitle,
        amount: currentPrice,
        durationDays: currentDuration,
        transactionReference: transactionRef.trim(),
        paymentScreenshot: screenshotPreview,
        status: 'PENDING',
        submittedAt: new Date().toISOString(),
      });
      setIsSubmitting(false);
      setStep(4);
    }, 600);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md animate-in fade-in duration-200">
      <div className="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-cyan-100 overflow-hidden flex flex-col max-h-[90vh]">
        {/* Header */}
        <div className="p-5 bg-gradient-to-r from-slate-900 via-cyan-950 to-slate-900 text-white flex items-center justify-between">
          <div className="flex items-center gap-2.5">
            <div className="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-400 to-orange-500 flex items-center justify-center text-slate-950 font-black shadow-lg shadow-orange-500/25">
              <Megaphone className="w-5 h-5 text-slate-950" />
            </div>
            <div>
              <h2 className="text-base sm:text-lg font-black tracking-tight text-white flex items-center gap-2">
                SargodhaMart Promote System
                <span className="text-[10px] px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 font-black uppercase">
                  ⭐ FEATURED
                </span>
              </h2>
              <p className="text-xs text-cyan-200/80 font-medium">
                Step {step} of 4: {step === 1 && 'Select Item'}
                {step === 2 && 'Review Plan & Perks'}
                {step === 3 && 'Payment & Proof'}
                {step === 4 && 'Request Submitted'}
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

        {/* Step Progress Bar */}
        <div className="w-full bg-slate-100 h-1.5 flex">
          <div
            className="bg-gradient-to-r from-cyan-500 to-amber-500 h-full transition-all duration-300"
            style={{ width: `${(step / 4) * 100}%` }}
          />
        </div>

        {/* Body Content */}
        <div className="p-6 overflow-y-auto flex-1">
          {error && (
            <div className="p-3.5 mb-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
              <AlertCircle className="w-4 h-4 text-rose-600 shrink-0" />
              <span>{error}</span>
            </div>
          )}

          {/* STEP 1: Select Product or Skill */}
          {step === 1 && (
            <div className="space-y-5">
              <div className="flex rounded-2xl bg-slate-100 p-1 border border-slate-200">
                <button
                  type="button"
                  onClick={() => {
                    setPromoType('PRODUCT');
                    setSelectedEntityId(0);
                  }}
                  className={`flex-1 py-2.5 rounded-xl text-xs font-bold transition-all ${
                    promoType === 'PRODUCT'
                      ? 'bg-white text-slate-900 shadow-sm border border-cyan-200'
                      : 'text-slate-600 hover:text-slate-950'
                  }`}
                >
                  📦 Promote My Product ({myListings.length})
                </button>
                <button
                  type="button"
                  onClick={() => {
                    setPromoType('SKILL');
                    setSelectedEntityId(0);
                  }}
                  className={`flex-1 py-2.5 rounded-xl text-xs font-bold transition-all ${
                    promoType === 'SKILL'
                      ? 'bg-white text-slate-900 shadow-sm border border-cyan-200'
                      : 'text-slate-600 hover:text-slate-950'
                  }`}
                >
                  💼 Promote My Digital Skill ({mySkills.length})
                </button>
              </div>

              <div>
                <label className="block text-xs font-black text-slate-800 mb-2 uppercase tracking-wider">
                  Select {promoType === 'PRODUCT' ? 'Product' : 'Digital Skill'} To Feature:
                </label>

                {promoType === 'PRODUCT' ? (
                  myListings.length === 0 ? (
                    <div className="p-8 text-center rounded-2xl bg-slate-50 border border-dashed border-slate-300">
                      <p className="text-xs text-slate-600 font-medium mb-2">
                        You do not have any published products yet.
                      </p>
                      <p className="text-[11px] text-slate-500">
                        Post an ad first and wait for approval before promoting.
                      </p>
                    </div>
                  ) : (
                    <div className="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                      {myListings.map((listing) => (
                        <div
                          key={listing.id}
                          onClick={() => {
                            setSelectedEntityId(listing.id);
                            setError('');
                          }}
                          className={`p-3.5 rounded-2xl border-2 cursor-pointer flex items-center justify-between gap-3 transition-all ${
                            selectedEntityId === listing.id
                              ? 'border-cyan-500 bg-cyan-50/60 shadow-sm'
                              : 'border-slate-200 bg-white hover:border-slate-300'
                          }`}
                        >
                          <div className="flex items-center gap-3">
                            <img
                              src={listing.images[0]}
                              alt={listing.title}
                              className="w-12 h-12 rounded-xl object-cover border border-slate-200 shrink-0"
                            />
                            <div>
                              <div className="text-xs font-black text-slate-900 line-clamp-1">{listing.title}</div>
                              <div className="text-[11px] font-bold text-emerald-700">
                                Rs. {listing.price.toLocaleString()} •{' '}
                                <span className="text-slate-500 font-medium">{listing.city}</span>
                              </div>
                            </div>
                          </div>
                          {selectedEntityId === listing.id && (
                            <div className="w-6 h-6 rounded-full bg-cyan-600 text-white flex items-center justify-center shrink-0">
                              <Check className="w-3.5 h-3.5" />
                            </div>
                          )}
                        </div>
                      ))}
                    </div>
                  )
                ) : mySkills.length === 0 ? (
                  <div className="p-8 text-center rounded-2xl bg-slate-50 border border-dashed border-slate-300">
                    <p className="text-xs text-slate-600 font-medium mb-2">
                      You do not have any approved Digital Skill profiles yet.
                    </p>
                    <p className="text-[11px] text-slate-500">
                      Create your Digital Skill profile in the dashboard first.
                    </p>
                  </div>
                ) : (
                  <div className="space-y-2.5 max-h-60 overflow-y-auto pr-1">
                    {mySkills.map((skill) => (
                      <div
                        key={skill.id}
                        onClick={() => {
                          setSelectedEntityId(skill.id);
                          setError('');
                        }}
                        className={`p-3.5 rounded-2xl border-2 cursor-pointer flex items-center justify-between gap-3 transition-all ${
                          selectedEntityId === skill.id
                            ? 'border-cyan-500 bg-cyan-50/60 shadow-sm'
                            : 'border-slate-200 bg-white hover:border-slate-300'
                        }`}
                      >
                        <div className="flex items-center gap-3">
                          <img
                            src={skill.profilePhoto}
                            alt={skill.fullName}
                            className="w-12 h-12 rounded-xl object-cover border border-slate-200 shrink-0"
                          />
                          <div>
                            <div className="text-xs font-black text-slate-900">{skill.professionalTitle}</div>
                            <div className="text-[11px] font-bold text-cyan-700">
                              {skill.mainSkill} •{' '}
                              <span className="text-slate-500 font-medium">{skill.experience}</span>
                            </div>
                          </div>
                        </div>
                        {selectedEntityId === skill.id && (
                          <div className="w-6 h-6 rounded-full bg-cyan-600 text-white flex items-center justify-center shrink-0">
                            <Check className="w-3.5 h-3.5" />
                          </div>
                        )}
                      </div>
                    ))}
                  </div>
                )}
              </div>

              {/* Fee Notice */}
              <div className="p-3.5 rounded-2xl bg-amber-50/80 border border-amber-200 text-amber-900 text-xs flex items-center gap-2.5">
                <Sparkles className="w-4 h-4 text-amber-600 shrink-0" />
                <div>
                  <strong className="font-bold">Fee Separation Note:</strong> Promotion is{' '}
                  <span className="font-bold underline">Rs. {currentPrice.toLocaleString()} for {currentDuration} days</span>.
                  It is completely separate from your one-time Rs. 1,000 lifetime seller activation fee.
                </div>
              </div>
            </div>
          )}

          {/* STEP 2: Promotion Plan & Benefits */}
          {step === 2 && (
            <div className="space-y-5">
              <div className="p-5 rounded-3xl bg-gradient-to-tr from-slate-900 via-cyan-950 to-slate-900 text-white shadow-xl relative overflow-hidden">
                <div className="relative z-10">
                  <div className="flex items-center justify-between gap-4 mb-3">
                    <span className="px-3 py-1 rounded-full text-xs font-black bg-amber-400 text-slate-950 flex items-center gap-1 shadow-xs">
                      ⭐ FEATURED PACKAGE
                    </span>
                    <span className="text-2xl font-black text-amber-400">
                      Rs. {currentPrice.toLocaleString()}{' '}
                      <span className="text-xs font-bold text-slate-300">/ {currentDuration} Days</span>
                    </span>
                  </div>

                  <h3 className="text-lg font-black text-white mb-2">{currentTitle}</h3>
                  <p className="text-xs text-cyan-200/90 leading-relaxed mb-4">
                    {promoType === 'PRODUCT'
                      ? settings?.promoProductDesc || 'Top featured placement across SargodhaMart homepage & search with ⭐ Featured badge.'
                      : settings?.promoSkillDesc || 'Priority placement in Digital Skills directory & search with ⭐ Featured badge.'}
                  </p>

                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs text-slate-200">
                    <div className="flex items-center gap-2">
                      <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
                      <span>Dedicated ⭐ FEATURED Gold Badge</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
                      <span>Top Placement in Homepage & Search</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
                      <span>Telegram Channel Broadcast Alert</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
                      <span>3x to 5x Higher Buyer Views</span>
                    </div>
                  </div>
                </div>
              </div>

              {/* Selected Target Summary */}
              <div className="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                <div className="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">
                  Selected Item For Promotion:
                </div>
                <div className="text-sm font-black text-slate-900">{selectedTitle}</div>
                <div className="text-xs text-slate-600 mt-1">
                  Duration: <strong>{currentDuration} Days</strong> • Auto-expires smoothly without automatic renewal or hidden fees.
                </div>
              </div>
            </div>
          )}

          {/* STEP 3: Payment & Screenshot Submission */}
          {step === 3 && (
            <div className="space-y-5">
              {/* Payment Accounts Box */}
              <div className="p-4 rounded-2xl bg-slate-900 text-white space-y-3">
                <div className="flex items-center justify-between">
                  <div className="text-xs font-bold text-amber-400 uppercase tracking-wider">
                    Official SargodhaMart Payment Accounts:
                  </div>
                  <div className="text-sm font-black text-white">
                    Amount: <span className="text-amber-400">Rs. {currentPrice.toLocaleString()}</span>
                  </div>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                  <div className="p-3 rounded-xl bg-slate-800/80 border border-slate-700">
                    <div className="font-bold text-emerald-400 mb-0.5">EasyPaisa Account:</div>
                    <div className="text-base font-black tracking-wide text-white">{settings?.easyPaisaNumber || '03127453108'}</div>
                    <div className="text-[11px] text-slate-400">Title: {settings?.paymentAccountTitle || 'Muhammad Akram Tayyab'}</div>
                  </div>
                  <div className="p-3 rounded-xl bg-slate-800/80 border border-slate-700">
                    <div className="font-bold text-rose-400 mb-0.5">JazzCash Account:</div>
                    <div className="text-base font-black tracking-wide text-white">{settings?.jazzCashNumber || '03127453108'}</div>
                    <div className="text-[11px] text-slate-400">Title: {settings?.paymentAccountTitle || 'Muhammad Akram Tayyab'}</div>
                  </div>
                </div>
                <div className="text-[11px] text-slate-400 italic">
                  Transfer exactly Rs. {currentPrice.toLocaleString()} PKR and save your receipt screenshot.
                </div>
              </div>

              {/* Transaction ID input */}
              <div>
                <label className="block text-xs font-black text-slate-800 mb-1.5 uppercase tracking-wider">
                  Transaction / Reference ID (TID): <span className="text-rose-500">*</span>
                </label>
                <input
                  type="text"
                  value={transactionRef}
                  onChange={(e) => setTransactionRef(e.target.value)}
                  placeholder="e.g. EP892182012 or TID10928172"
                  className="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-900 focus:outline-hidden focus:border-cyan-500"
                />
              </div>

              {/* Screenshot Upload */}
              <div>
                <label className="block text-xs font-black text-slate-800 mb-1.5 uppercase tracking-wider">
                  Upload Payment Screenshot: <span className="text-rose-500">*</span>
                </label>
                <div className="flex items-center gap-4">
                  <label className="flex-1 border-2 border-dashed border-slate-300 hover:border-cyan-500 rounded-2xl p-4 text-center cursor-pointer bg-slate-50 hover:bg-cyan-50/50 transition-colors">
                    <Upload className="w-5 h-5 text-slate-400 mx-auto mb-1.5" />
                    <span className="text-xs font-bold text-slate-700 block">Click to upload transfer receipt</span>
                    <span className="text-[10px] text-slate-400 block mt-0.5">JPG, PNG, WebP up to 5MB</span>
                    <input type="file" accept="image/*" onChange={handleFileChange} className="hidden" />
                  </label>

                  {screenshotPreview && (
                    <div className="w-20 h-20 rounded-2xl border-2 border-emerald-500 overflow-hidden relative shrink-0">
                      <img src={screenshotPreview} alt="Receipt preview" className="w-full h-full object-cover" />
                      <div className="absolute top-1 right-1 w-4 h-4 bg-emerald-500 text-white rounded-full flex items-center justify-center text-[9px]">
                        ✓
                      </div>
                    </div>
                  )}
                </div>
              </div>
            </div>
          )}

          {/* STEP 4: Success confirmation */}
          {step === 4 && (
            <div className="py-6 text-center space-y-4">
              <div className="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto shadow-inner">
                <CheckCircle2 className="w-10 h-10" />
              </div>
              <h3 className="text-lg font-black text-slate-900">
                Promotion Request Submitted!
              </h3>
              <p className="text-xs text-slate-600 max-w-md mx-auto leading-relaxed">
                Your promotion request for <strong>{selectedTitle}</strong> (Trx ID: {transactionRef}) has been
                submitted and is waiting for Admin verification.
              </p>
              <div className="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-medium max-w-md mx-auto text-left">
                ⚠️ <strong>Important:</strong> The item will <strong>NOT</strong> become promoted immediately. Once the
                administrator verifies your payment receipt in the Admin Panel, your item will receive the ⭐ FEATURED badge
                and 15 days of top priority visibility.
              </div>
              <div className="pt-2">
                <button
                  onClick={onClose}
                  className="px-6 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition-all shadow-md"
                >
                  View in Promotion History
                </button>
              </div>
            </div>
          )}
        </div>

        {/* Footer Navigation Buttons */}
        {step < 4 && (
          <div className="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
            {step > 1 ? (
              <button
                type="button"
                onClick={() => setStep((s) => (s - 1) as 1 | 2 | 3)}
                className="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-100 flex items-center gap-1.5 transition-all"
              >
                <ArrowLeft className="w-3.5 h-3.5" /> Back
              </button>
            ) : (
              <div />
            )}

            {step === 1 && (
              <button
                type="button"
                disabled={!selectedEntityId}
                onClick={() => setStep(2)}
                className="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-bold text-xs disabled:opacity-40 disabled:cursor-not-allowed shadow-md shadow-cyan-600/25 flex items-center gap-1.5 transition-all"
              >
                Continue to Plan <ArrowRight className="w-3.5 h-3.5" />
              </button>
            )}

            {step === 2 && (
              <button
                type="button"
                onClick={() => setStep(3)}
                className="px-5 py-2.5 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-bold text-xs shadow-md shadow-cyan-600/25 flex items-center gap-1.5 transition-all"
              >
                Proceed to Payment <ArrowRight className="w-3.5 h-3.5" />
              </button>
            )}

            {step === 3 && (
              <button
                type="button"
                disabled={isSubmitting || !transactionRef.trim() || !screenshotPreview}
                onClick={handleSubmit}
                className="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-black text-xs disabled:opacity-40 disabled:cursor-not-allowed shadow-lg shadow-orange-500/30 flex items-center gap-1.5 transition-all"
              >
                {isSubmitting ? 'Submitting...' : 'Submit Promotion Request'}
              </button>
            )}
          </div>
        )}
      </div>
    </div>
  );
};
