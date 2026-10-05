import React, { useState } from 'react';
import { X, ShieldCheck, CheckCircle2, Clock, Upload, ArrowRight, ExternalLink, MessageCircle } from 'lucide-react';
import { UserProfile, ActivationPayment, SiteSettings } from '../types';
import DynamicPaymentQR from './DynamicPaymentQR';

interface ActivationModalProps {
  currentUser: UserProfile;
  isOpen: boolean;
  onClose: () => void;
  onSubmitActivation: (data: {
    method: 'EasyPaisa' | 'JazzCash';
    senderNumber: string;
    transactionId: string;
    paymentScreenshot: string;
    whatsappScreenshot: string;
  }) => void;
  existingPendingPayment?: ActivationPayment | null;
  onOpenAdminPanel?: () => void;
  settings?: SiteSettings;
}

export const ActivationModal: React.FC<ActivationModalProps> = ({
  currentUser,
  isOpen,
  onClose,
  onSubmitActivation,
  existingPendingPayment,
  onOpenAdminPanel,
  settings,
}) => {
  const [method, setMethod] = useState<'EasyPaisa' | 'JazzCash'>('EasyPaisa');
  const [senderNumber, setSenderNumber] = useState(currentUser.mobile);
  const [transactionId, setTransactionId] = useState('');
  const [paymentScreenshot, setPaymentScreenshot] = useState<string>('');
  const [whatsappScreenshot, setWhatsappScreenshot] = useState<string>('');
  const [error, setError] = useState('');

  if (!isOpen) return null;

  const fee = settings?.activationFee || 1000;
  const feeFormatted = `Rs. ${fee.toLocaleString()}`;
  const accountTitle = settings?.paymentAccountTitle || 'Muhammad Akram Tayyab';
  const easyPaisaNum = settings?.easyPaisaNumber || '03127453108';
  const jazzCashNum = settings?.jazzCashNumber || '03127453108';
  const activeAccountNumber = method === 'EasyPaisa' ? easyPaisaNum : jazzCashNum;
  const channelUrl = settings?.whatsappChannelUrl || 'https://whatsapp.com/channel/0029Vb8bmhAGk1Fzze6iTX0g';
  const helpline = settings?.officialWhatsappNumber || '03127453108';
  const isPaymentReq = settings ? settings.isPaymentScreenshotRequired : true;
  const isWhatsappReq = settings ? settings.isWhatsappFollowScreenshotRequired : true;

  const handlePaymentFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (event) => {
        setPaymentScreenshot(event.target?.result as string);
      };
      reader.readAsDataURL(file);
    }
  };

  const handleWhatsappFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = (event) => {
        setWhatsappScreenshot(event.target?.result as string);
      };
      reader.readAsDataURL(file);
    }
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!senderNumber || !transactionId) {
      setError('Please provide sender number and Transaction ID (TRX ID).');
      return;
    }
    if (isPaymentReq && !paymentScreenshot) {
      setError(`Please upload your ${feeFormatted} payment transfer screenshot.`);
      return;
    }

    onSubmitActivation({
      method,
      senderNumber,
      transactionId,
      paymentScreenshot: paymentScreenshot || 'proof-placeholder',
      whatsappScreenshot: whatsappScreenshot || '',
    });
  };

  // If user already has a pending submission under admin review
  if (existingPendingPayment && existingPendingPayment.status === 'pending') {
    return (
      <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-md overflow-y-auto">
        <div className="relative w-full max-w-lg bg-white border border-amber-200 rounded-3xl p-6 md:p-8 shadow-[0_20px_60px_-15px_rgba(245,158,11,0.25)] my-8 text-center">
          <button
            onClick={onClose}
            className="absolute top-5 right-5 p-2 rounded-xl text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-all"
          >
            <X className="w-5 h-5" />
          </button>

          {/* Stepper Status */}
          <div className="flex items-center justify-center gap-2 mb-6 text-[10px] font-bold">
            <span className="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-300">
              1. Registered ✓
            </span>
            <span className="text-slate-400">→</span>
            <span className="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-300">
              2. Proofs Uploaded ✓
            </span>
            <span className="text-slate-400">→</span>
            <span className="px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-300 animate-pulse font-extrabold shadow-[0_0_10px_rgba(245,158,11,0.2)]">
              3. Admin Reviewing
            </span>
            <span className="text-slate-400">→</span>
            <span className="px-2.5 py-1 rounded-full bg-slate-100 text-slate-400">
              4. Active Dashboard
            </span>
          </div>

          <div className="w-16 h-16 rounded-3xl bg-amber-50 border border-amber-300 text-amber-600 flex items-center justify-center mx-auto mb-4 shadow-[0_0_15px_rgba(245,158,11,0.2)]">
            <Clock className="w-8 h-8 animate-spin" style={{ animationDuration: '6s' }} />
          </div>

          <h3 className="text-xl font-black text-slate-900 mb-2">Activation Under Review</h3>
          <p className="text-slate-600 text-xs max-w-sm mx-auto mb-6 font-medium">
            {settings?.activationUnderReviewNotice ||
              `Your ${feeFormatted} transfer proof (TRX: ${existingPendingPayment.transactionId}) and WhatsApp channel follow screenshot have been submitted to the verification team.`}
          </p>

          <div className="bg-slate-50 p-4 rounded-2xl border border-slate-200 text-left text-xs mb-6 space-y-2">
            <div className="flex justify-between">
              <span className="text-slate-500 font-medium">Account Name:</span>
              <span className="font-bold text-slate-900">{currentUser.name}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-slate-500 font-medium">Verification Fee:</span>
              <span className="font-bold text-emerald-700">{feeFormatted} (Paid)</span>
            </div>
            <div className="flex justify-between">
              <span className="text-slate-500 font-medium">Expected Approval:</span>
              <span className="text-cyan-700 font-bold">Within 15-30 minutes</span>
            </div>
          </div>

          <div className="flex flex-col gap-2">
            <a
              href={`https://wa.me/92${helpline.replace(/^0/, '')}?text=${encodeURIComponent(`Salam! I submitted activation for SargodhaMart. TRX: ${existingPendingPayment.transactionId}, User: ${currentUser.name}`)}`}
              target="_blank"
              rel="noreferrer"
              className="w-full py-3 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-500 text-white flex items-center justify-center gap-2 transition-all shadow-lg shadow-emerald-600/25"
            >
              <MessageCircle className="w-4 h-4" />
              <span>Contact Admin on WhatsApp ({helpline})</span>
            </a>

            {onOpenAdminPanel && (
              <button
                onClick={() => {
                  onClose();
                  onOpenAdminPanel();
                }}
                className="w-full py-2.5 rounded-xl font-bold text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition-all"
              >
                Switch to Admin Panel to Approve Instantly
              </button>
            )}
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-md overflow-y-auto">
      <div className="relative w-full max-w-2xl bg-white border border-cyan-200 rounded-3xl p-6 md:p-8 shadow-[0_20px_60px_-15px_rgba(6,182,212,0.25)] my-8">
        <button
          onClick={onClose}
          className="absolute top-5 right-5 p-2 rounded-xl text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-all"
        >
          <X className="w-5 h-5" />
        </button>

        {/* Stepper Status */}
        <div className="flex items-center gap-2 mb-6 text-[10px] font-bold overflow-x-auto pb-1">
          <span className="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-300">
            1. Registered ✓
          </span>
          <span className="text-slate-400">→</span>
          <span className="px-2.5 py-1 rounded-full bg-cyan-50 text-cyan-800 border border-cyan-300 font-extrabold shadow-[0_0_10px_rgba(6,182,212,0.2)]">
            2. Payment & WhatsApp Proof
          </span>
          <span className="text-slate-400">→</span>
          <span className="px-2.5 py-1 rounded-full bg-slate-100 text-slate-400">
            3. Admin Verification
          </span>
          <span className="text-slate-400">→</span>
          <span className="px-2.5 py-1 rounded-full bg-slate-100 text-slate-400">
            4. Unlimited Posting
          </span>
        </div>

        {/* Header */}
        <div className="flex items-center gap-3 mb-6">
          <div className="w-12 h-12 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/25">
            <ShieldCheck className="w-6 h-6" />
          </div>
          <div>
            <h2 className="text-xl font-bold text-slate-900">Lifetime Seller Activation</h2>
            <p className="text-slate-500 text-xs font-medium">
              One-time {feeFormatted} fee for lifetime unlimited free product listings & jobs
            </p>
          </div>
        </div>

        {error && (
          <div className="p-3 rounded-xl bg-rose-50 border border-rose-300 text-rose-700 text-xs mb-4 font-semibold">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Bank / Wallet Details Card */}
          <div className="bg-slate-50 border border-cyan-200 rounded-2xl p-4 md:p-5 shadow-xs">
            <div className="flex flex-col md:flex-row items-center gap-6">
              <div className="shrink-0 flex flex-col items-center bg-white p-2 rounded-2xl border border-slate-200 shadow-sm">
                <DynamicPaymentQR
                  paymentNumber={activeAccountNumber}
                  accountName={accountTitle}
                  feeAmount={fee}
                />
                <span className="text-[10px] text-slate-500 font-bold mt-1">Scan via {method}</span>
              </div>

              <div className="flex-1 space-y-2 text-xs">
                <div className="flex gap-2 mb-3">
                  <button
                    type="button"
                    onClick={() => setMethod('EasyPaisa')}
                    className={`flex-1 py-1.5 rounded-xl font-bold text-xs transition-all ${
                      method === 'EasyPaisa'
                        ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/25'
                        : 'bg-white text-slate-600 border border-slate-200'
                    }`}
                  >
                    EasyPaisa
                  </button>
                  <button
                    type="button"
                    onClick={() => setMethod('JazzCash')}
                    className={`flex-1 py-1.5 rounded-xl font-bold text-xs transition-all ${
                      method === 'JazzCash'
                        ? 'bg-rose-600 text-white shadow-md shadow-rose-600/25'
                        : 'bg-white text-slate-600 border border-slate-200'
                    }`}
                  >
                    JazzCash
                  </button>
                </div>

                <div className="flex justify-between py-1 border-b border-slate-200">
                  <span className="text-slate-500 font-medium">Account Number:</span>
                  <span className="font-mono font-bold text-cyan-800 text-sm">{activeAccountNumber}</span>
                </div>
                <div className="flex justify-between py-1 border-b border-slate-200">
                  <span className="text-slate-500 font-medium">Account Title:</span>
                  <span className="font-bold text-slate-900">{accountTitle}</span>
                </div>
                <div className="flex justify-between py-1">
                  <span className="text-slate-500 font-medium">Lifetime Activation Fee:</span>
                  <span className="font-bold text-emerald-700 text-sm">{feeFormatted} (One-Time)</span>
                </div>
              </div>
            </div>
          </div>

          {/* Step 1: Payment Verification Form */}
          <div className="bg-slate-50/80 border border-cyan-200 rounded-2xl p-4 space-y-3">
            <h3 className="text-xs font-bold text-cyan-800 uppercase tracking-wider flex items-center gap-1.5">
              <span>1️⃣</span> Payment Transfer Details ({feeFormatted})
            </h3>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div>
                <label className="block text-[11px] font-semibold text-slate-800 mb-1">
                  Your Sender Mobile Number *
                </label>
                <input
                  type="text"
                  value={senderNumber}
                  onChange={(e) => setSenderNumber(e.target.value)}
                  placeholder="03001234567"
                  className="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs focus:outline-none focus:border-cyan-500 font-medium"
                  required
                />
              </div>

              <div>
                <label className="block text-[11px] font-semibold text-slate-800 mb-1">
                  Transaction ID (TRX ID / TID) *
                </label>
                <input
                  type="text"
                  value={transactionId}
                  onChange={(e) => setTransactionId(e.target.value)}
                  placeholder="e.g. EP8291038294"
                  className="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-xs font-mono focus:outline-none focus:border-cyan-500 font-bold"
                  required
                />
              </div>
            </div>

            {isPaymentReq && (
              <div>
                <label className="block text-[11px] font-semibold text-slate-800 mb-1">
                  Upload Payment Transfer Screenshot *
                </label>
                <div className="flex items-center gap-3">
                  <input
                    type="file"
                    accept="image/*"
                    onChange={handlePaymentFileChange}
                    className="text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-cyan-100 file:text-cyan-800 hover:file:bg-cyan-200 cursor-pointer"
                  />
                  {paymentScreenshot && (
                    <span className="text-[11px] text-emerald-700 font-bold flex items-center gap-1">
                      <CheckCircle2 className="w-3.5 h-3.5" /> Screenshot Attached
                    </span>
                  )}
                </div>
              </div>
            )}
          </div>

          {/* Step 2: WhatsApp Channel (Optional Community Link) */}
          <div className="bg-slate-50/80 border border-emerald-200 rounded-2xl p-4 space-y-3">
            <div className="flex items-center justify-between">
              <h3 className="text-xs font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-1.5">
                <span>📲</span> Official WhatsApp Channel (Optional)
              </h3>
              <a
                href={channelUrl}
                target="_blank"
                rel="noreferrer"
                className="text-[11px] font-bold text-emerald-700 hover:text-emerald-900 hover:underline flex items-center gap-1"
              >
                Join Channel <ExternalLink className="w-3 h-3" />
              </a>
            </div>

            <p className="text-slate-600 text-xs font-medium">
              Optional: Join our official SargodhaMart WhatsApp Channel for daily local announcements, security updates, and community alerts. Membership is 100% optional and not required for activation.
            </p>

            <div>
              <label className="block text-[11px] font-semibold text-slate-700 mb-1">
                Optional: Upload WhatsApp Channel Follow Screenshot (Not Mandatory)
              </label>
              <div className="flex items-center gap-3">
                <input
                  type="file"
                  accept="image/*"
                  onChange={handleWhatsappFileChange}
                  className="text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-100 file:text-emerald-800 hover:file:bg-emerald-200 cursor-pointer"
                />
                {whatsappScreenshot && (
                  <span className="text-[11px] text-emerald-700 font-bold flex items-center gap-1">
                    <CheckCircle2 className="w-3.5 h-3.5" /> Screenshot Attached
                  </span>
                )}
              </div>
            </div>
          </div>

          {/* Submit Button */}
          <div className="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-all"
            >
              Cancel
            </button>
            <button
              type="submit"
              className="px-6 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-cyan-500 via-blue-600 to-fuchsia-600 hover:from-cyan-400 hover:via-blue-500 hover:to-fuchsia-500 text-white shadow-lg shadow-cyan-500/25 transition-all flex items-center gap-2 hover:shadow-[0_0_15px_rgba(6,182,212,0.4)]"
            >
              <CheckCircle2 className="w-4 h-4" />
              Submit Proofs for Admin Verification
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
