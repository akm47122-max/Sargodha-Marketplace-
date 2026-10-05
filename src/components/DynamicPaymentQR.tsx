import React, { useState, useEffect, useRef } from 'react';
import QRCode from 'qrcode';
import {
  QrCode,
  Copy,
  Check,
  Maximize2,
  Download,
  PhoneCall,
  ShieldCheck,
  Sparkles,
  Smartphone,
  Info,
  CheckCircle2,
  Layers,
  X
} from 'lucide-react';

interface DynamicPaymentQRProps {
  paymentNumber: string;
  accountName: string;
  feeAmount: number;
  onNumberCopied?: () => void;
}

export type QRFormat = 'tel' | 'number' | 'raast' | 'invoice';
export type QRTheme = 'easypaisa' | 'jazzcash' | 'classic';

export const DynamicPaymentQR: React.FC<DynamicPaymentQRProps> = ({
  paymentNumber,
  accountName,
  feeAmount,
  onNumberCopied,
}) => {
  const [format, setFormat] = useState<QRFormat>('number');
  const [theme, setTheme] = useState<QRTheme>('easypaisa');
  const [qrSvg, setQrSvg] = useState<string>('');
  const [isCopied, setIsCopied] = useState<boolean>(false);
  const [isModalOpen, setIsModalOpen] = useState<boolean>(false);
  const [showGuide, setShowGuide] = useState<boolean>(false);
  const canvasRef = useRef<HTMLCanvasElement | null>(null);

  // Compute the encoded string based on format
  const getEncodedData = () => {
    switch (format) {
      case 'tel':
        return `tel:${paymentNumber}`;
      case 'raast':
        return `raast://pay?recipient=${paymentNumber}&name=${encodeURIComponent(accountName)}&amount=${feeAmount}`;
      case 'invoice':
        return `SARGODHAMART-PAYMENT:${paymentNumber}|${accountName}|PKR${feeAmount}|ONE-TIME-SELLER-ACTIVATION`;
      case 'number':
      default:
        return paymentNumber;
    }
  };

  // Determine colors based on brand theme
  const getColors = () => {
    switch (theme) {
      case 'easypaisa':
        return { dark: '#047857', light: '#ffffff', border: 'border-emerald-300', bg: 'bg-emerald-50', text: 'text-emerald-800' };
      case 'jazzcash':
        return { dark: '#c2410c', light: '#ffffff', border: 'border-orange-300', bg: 'bg-orange-50', text: 'text-orange-800' };
      case 'classic':
      default:
        return { dark: '#0f172a', light: '#ffffff', border: 'border-slate-300', bg: 'bg-slate-50', text: 'text-slate-800' };
    }
  };

  const currentColors = getColors();
  const currentData = getEncodedData();

  // Generate QR code SVG whenever format, data, or theme changes
  useEffect(() => {
    let isSubscribed = true;

    QRCode.toString(
      currentData,
      {
        type: 'svg',
        errorCorrectionLevel: 'H',
        margin: 1,
        color: {
          dark: currentColors.dark,
          light: currentColors.light,
        },
      },
      (err, svgString) => {
        if (!err && isSubscribed && svgString) {
          setQrSvg(svgString);
        }
      }
    );

    // Also draw on canvas for quick high-res image download
    if (canvasRef.current) {
      QRCode.toCanvas(
        canvasRef.current,
        currentData,
        {
          width: 320,
          margin: 2,
          errorCorrectionLevel: 'H',
          color: {
            dark: currentColors.dark,
            light: currentColors.light,
          },
        },
        () => {}
      );
    }

    return () => {
      isSubscribed = false;
    };
  }, [currentData, currentColors.dark, currentColors.light]);

  const handleCopy = () => {
    navigator.clipboard.writeText(paymentNumber);
    setIsCopied(true);
    if (onNumberCopied) onNumberCopied();
    setTimeout(() => setIsCopied(false), 2000);
  };

  const handleDownload = () => {
    if (!canvasRef.current) return;
    const link = document.createElement('a');
    link.download = `sargodhamart-easypaisa-${paymentNumber}.png`;
    link.href = canvasRef.current.toDataURL('image/png');
    link.click();
  };

  return (
    <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs space-y-4">
      {/* Top Banner / Receiver Info */}
      <div className="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-100">
        <div className="flex items-center gap-2">
          <div className="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold">
            <QrCode className="w-5 h-5 text-emerald-700" />
          </div>
          <div>
            <span className="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
              Official Pakistani Mobile Wallet
            </span>
            <h3 className="text-sm font-extrabold text-slate-900 flex items-center gap-1.5">
              <span>Dynamic QR Code Generator</span>
              <span className="text-[10px] bg-emerald-100 text-emerald-800 font-semibold px-2 py-0.5 rounded-full border border-emerald-200">
                Instant Scan
              </span>
            </h3>
          </div>
        </div>

        {/* Brand Theme Selector */}
        <div className="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-[11px]">
          <button
            type="button"
            onClick={() => setTheme('easypaisa')}
            className={`px-2.5 py-1 rounded-lg font-bold transition-all ${
              theme === 'easypaisa'
                ? 'bg-emerald-600 text-white shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            EasyPaisa
          </button>
          <button
            type="button"
            onClick={() => setTheme('jazzcash')}
            className={`px-2.5 py-1 rounded-lg font-bold transition-all ${
              theme === 'jazzcash'
                ? 'bg-orange-600 text-white shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            JazzCash
          </button>
          <button
            type="button"
            onClick={() => setTheme('classic')}
            className={`px-2 py-1 rounded-lg font-bold transition-all ${
              theme === 'classic'
                ? 'bg-slate-800 text-white shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            Mono
          </button>
        </div>
      </div>

      {/* Main Grid: Left Details & Right QR Generator Canvas */}
      <div className="grid grid-cols-1 md:grid-cols-12 gap-5 items-center">
        {/* Account Details & Quick Controls */}
        <div className="md:col-span-7 space-y-3">
          <div className="bg-slate-50 border border-slate-200 rounded-xl p-3.5 space-y-2.5">
            <div className="flex items-center justify-between">
              <span className="text-[11px] font-semibold text-slate-500">Receiver Account Title</span>
              <span className="text-[11px] font-bold text-emerald-800 flex items-center gap-1">
                <ShieldCheck className="w-3.5 h-3.5" /> Verified Admin Account
              </span>
            </div>
            <div className="text-base font-extrabold text-slate-900">{accountName}</div>

            <div className="pt-2 border-t border-slate-200/80">
              <span className="text-[11px] font-semibold text-slate-500 block mb-1">
                EasyPaisa / JazzCash Mobile Number
              </span>
              <div className="flex items-center gap-2">
                <span className="text-xl font-extrabold font-mono text-emerald-800 tracking-wider bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-xs">
                  {paymentNumber}
                </span>
                <button
                  type="button"
                  onClick={handleCopy}
                  className={`px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1.5 border transition-all ${
                    isCopied
                      ? 'bg-emerald-600 text-white border-emerald-600'
                      : 'bg-white hover:bg-slate-50 text-slate-700 border-slate-300'
                  }`}
                >
                  {isCopied ? (
                    <>
                      <Check className="w-3.5 h-3.5" /> Copied!
                    </>
                  ) : (
                    <>
                      <Copy className="w-3.5 h-3.5" /> Copy
                    </>
                  )}
                </button>
                <a
                  href={`tel:${paymentNumber}`}
                  className="px-2.5 py-1.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-lg text-xs font-bold flex items-center gap-1"
                  title="Direct Dial / Call"
                >
                  <PhoneCall className="w-3.5 h-3.5 text-emerald-700" />
                </a>
              </div>
            </div>

            <div className="flex items-center justify-between pt-2 border-t border-slate-200/80 text-xs">
              <span className="text-slate-600">Required Activation Fee:</span>
              <span className="font-extrabold text-emerald-700 text-sm">
                Rs. {feeAmount.toLocaleString()} PKR <span className="text-[10px] font-normal text-slate-500">(One-Time)</span>
              </span>
            </div>
          </div>

          {/* QR Payload Format Selector */}
          <div className="space-y-1.5">
            <span className="text-[11px] font-bold text-slate-600 flex items-center gap-1">
              <Layers className="w-3 h-3 text-slate-400" /> Dynamic QR Payload Format:
            </span>
            <div className="grid grid-cols-3 gap-1.5 text-[10px]">
              <button
                type="button"
                onClick={() => setFormat('number')}
                className={`py-1.5 px-2 rounded-lg font-semibold border text-center transition-all ${
                  format === 'number'
                    ? 'bg-emerald-50 text-emerald-800 border-emerald-300 font-bold'
                    : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
                }`}
              >
                EasyPaisa Number
              </button>
              <button
                type="button"
                onClick={() => setFormat('tel')}
                className={`py-1.5 px-2 rounded-lg font-semibold border text-center transition-all ${
                  format === 'tel'
                    ? 'bg-emerald-50 text-emerald-800 border-emerald-300 font-bold'
                    : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
                }`}
              >
                Direct Dial (tel:)
              </button>
              <button
                type="button"
                onClick={() => setFormat('raast')}
                className={`py-1.5 px-2 rounded-lg font-semibold border text-center transition-all ${
                  format === 'raast'
                    ? 'bg-emerald-50 text-emerald-800 border-emerald-300 font-bold'
                    : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'
                }`}
              >
                Raast / Deep Link
              </button>
            </div>
            <p className="text-[10px] text-slate-400 font-mono truncate">
              Payload: {currentData}
            </p>
          </div>
        </div>

        {/* QR Code Visualization Card */}
        <div className="md:col-span-5 flex flex-col items-center justify-center">
          <div className="relative group bg-white p-3 rounded-2xl border-2 border-slate-200 shadow-sm flex flex-col items-center">
            {/* Corner Badge */}
            <div className="absolute -top-2.5 bg-slate-900 text-white text-[9px] font-extrabold uppercase px-2.5 py-0.5 rounded-full shadow-xs flex items-center gap-1">
              <Sparkles className="w-2.5 h-2.5 text-amber-400" />
              {theme === 'easypaisa' ? 'EasyPaisa Pay' : theme === 'jazzcash' ? 'JazzCash Pay' : 'Scan to Pay'}
            </div>

            {/* SVG Dynamic QR Code Render */}
            <div
              className="w-44 h-44 p-2 bg-white rounded-xl flex items-center justify-center cursor-pointer transition-transform hover:scale-102"
              onClick={() => setIsModalOpen(true)}
              title="Click to enlarge QR code"
              dangerouslySetInnerHTML={{ __html: qrSvg }}
            />

            {/* Hidden canvas for downloading crisp PNG */}
            <canvas ref={canvasRef} className="hidden" />

            {/* Under-QR Details */}
            <div className="text-center mt-2 space-y-1">
              <div className="text-xs font-bold text-slate-800 font-mono tracking-wide">
                {paymentNumber}
              </div>
              <div className="text-[10px] text-slate-500 font-medium">
                {accountName} &bull; Rs. {feeAmount}
              </div>
            </div>

            {/* Quick Action Buttons for QR */}
            <div className="flex items-center gap-1.5 mt-3 pt-2 border-t border-slate-100 w-full justify-center">
              <button
                type="button"
                onClick={() => setIsModalOpen(true)}
                className="px-2.5 py-1 text-[11px] font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg flex items-center gap-1"
                title="Enlarge QR Code"
              >
                <Maximize2 className="w-3 h-3 text-slate-600" /> Enlarge
              </button>
              <button
                type="button"
                onClick={handleDownload}
                className="px-2.5 py-1 text-[11px] font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg flex items-center gap-1"
                title="Save QR Code image"
              >
                <Download className="w-3 h-3 text-slate-600" /> Save PNG
              </button>
            </div>
          </div>
          <span className="text-[10px] text-slate-500 mt-2 text-center flex items-center gap-1">
            <Smartphone className="w-3 h-3 text-emerald-700" />
            Open EasyPaisa or JazzCash on your phone & scan
          </span>
        </div>
      </div>

      {/* Accordion / Step-by-Step Payment Instructions */}
      <div className="border-t border-slate-100 pt-3">
        <button
          type="button"
          onClick={() => setShowGuide(!showGuide)}
          className="w-full flex items-center justify-between text-left text-xs font-bold text-slate-700 hover:text-emerald-800 transition-colors py-1"
        >
          <span className="flex items-center gap-1.5">
            <Info className="w-4 h-4 text-emerald-700" />
            How to send Rs. 500 via EasyPaisa or JazzCash? (5 Easy Steps)
          </span>
          <span className="text-[11px] text-emerald-700 font-semibold">
            {showGuide ? 'Hide Instructions ↑' : 'Show Instructions ↓'}
          </span>
        </button>

        {showGuide && (
          <div className="mt-3 p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-2.5 text-slate-700">
            <div className="flex items-start gap-2">
              <span className="w-5 h-5 rounded-full bg-emerald-600 text-white font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5">
                1
              </span>
              <div>
                <strong>Open your EasyPaisa or JazzCash App</strong> on your mobile phone and log in.
              </div>
            </div>
            <div className="flex items-start gap-2">
              <span className="w-5 h-5 rounded-full bg-emerald-600 text-white font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5">
                2
              </span>
              <div>
                Tap <strong>&apos;Send Money&apos;</strong> &rarr; select <strong>&apos;EasyPaisa Transfer&apos;</strong> (or JazzCash to JazzCash / Raast). Alternatively tap the <strong>QR Scanner icon</strong> in your app to scan the code above.
              </div>
            </div>
            <div className="flex items-start gap-2">
              <span className="w-5 h-5 rounded-full bg-emerald-600 text-white font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5">
                3
              </span>
              <div>
                Enter receiver mobile number: <strong className="font-mono text-emerald-800">{paymentNumber}</strong> and verify receiver title shows: <strong>{accountName}</strong>.
              </div>
            </div>
            <div className="flex items-start gap-2">
              <span className="w-5 h-5 rounded-full bg-emerald-600 text-white font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5">
                4
              </span>
              <div>
                Enter exact amount <strong>Rs. {feeAmount} PKR</strong> and confirm transfer.
              </div>
            </div>
            <div className="flex items-start gap-2">
              <span className="w-5 h-5 rounded-full bg-emerald-600 text-white font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5">
                5
              </span>
              <div>
                Take a <strong>screenshot of the confirmation receipt</strong>, note down the <strong>Transaction ID (TRX ID)</strong>, and submit them in the form below!
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Enlarged QR Code Modal for second-device scanning */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl space-y-4 text-center relative animate-in fade-in zoom-in-95 duration-200">
            <button
              type="button"
              onClick={() => setIsModalOpen(false)}
              className="absolute top-4 right-4 text-slate-400 hover:text-slate-700 p-1.5 rounded-full bg-slate-100"
            >
              <X className="w-4 h-4" />
            </button>

            <div>
              <span className="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">
                High-Resolution QR Code
              </span>
              <h4 className="text-base font-extrabold text-slate-900">
                Scan with EasyPaisa / JazzCash
              </h4>
            </div>

            <div className="p-4 bg-white border-2 border-emerald-300 rounded-2xl inline-block shadow-inner">
              <div
                className="w-64 h-64 mx-auto flex items-center justify-center"
                dangerouslySetInnerHTML={{ __html: qrSvg }}
              />
            </div>

            <div className="space-y-1">
              <div className="text-lg font-mono font-extrabold text-emerald-800">
                {paymentNumber}
              </div>
              <div className="text-xs font-bold text-slate-700">
                {accountName}
              </div>
              <div className="text-xs text-slate-500">
                One-Time Seller Activation Fee: <strong>Rs. {feeAmount} PKR</strong>
              </div>
            </div>

            <div className="flex items-center gap-2 pt-2">
              <button
                type="button"
                onClick={handleCopy}
                className="flex-1 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl flex items-center justify-center gap-1.5"
              >
                {isCopied ? <Check className="w-4 h-4 text-emerald-600" /> : <Copy className="w-4 h-4" />}
                {isCopied ? 'Copied!' : 'Copy Number'}
              </button>
              <button
                type="button"
                onClick={handleDownload}
                className="flex-1 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-xl flex items-center justify-center gap-1.5 shadow-xs"
              >
                <Download className="w-4 h-4" /> Save QR Code
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};
export default DynamicPaymentQR;
