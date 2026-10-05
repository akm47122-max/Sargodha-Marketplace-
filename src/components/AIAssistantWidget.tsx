import React, { useState, useRef, useEffect } from 'react';
import { Bot, X, Send, Sparkles, ShieldCheck, ArrowRight, ShoppingBag, Briefcase, Zap, MapPin, Compass } from 'lucide-react';
import { UserProfile, SiteSettings, AdvertisingPackage, Advertisement } from '../types';

interface AIAssistantWidgetProps {
  currentUser: UserProfile | null;
  settings?: SiteSettings;
  advertisingPackages?: AdvertisingPackage[];
  advertisements?: Advertisement[];
  onNavigate: (view: 'home' | 'jobs' | 'skills' | 'dashboard' | 'admin') => void;
  onOpenActivation?: () => void;
  onOpenPromoteModal?: () => void;
  onOpenCreateAd?: () => void;
}

interface Message {
  id: string;
  sender: 'bot' | 'user';
  text: string;
  timestamp: string;
  action?: {
    label: string;
    onClick: () => void;
  };
}

export const AIAssistantWidget: React.FC<AIAssistantWidgetProps> = ({
  currentUser,
  settings,
  advertisingPackages = [],
  advertisements = [],
  onNavigate,
  onOpenActivation,
  onOpenPromoteModal,
  onOpenCreateAd,
}) => {
  const [isOpen, setIsOpen] = useState(false);
  const [input, setInput] = useState('');
  const [isTyping, setIsTyping] = useState(false);
  const messagesEndRef = useRef<HTMLDivElement>(null);

  const promoPrice = settings?.promoProductPrice || 1000;
  const promoDays = settings?.promoProductDuration || 15;
  const activationFee = settings?.activationFee || 1000;

  const [messages, setMessages] = useState<Message[]>([
    {
      id: 'welcome',
      sender: 'bot',
      text: `Assalam-o-Alaikum ${currentUser ? currentUser.name : 'Bhai'}! 👋\n\nMain **SargodhaMart AI Assistant** hoon. Main aapko platform ke rules, **Advertising Packages**, Promote System, Digital Skills, aur navigation mein live guide kar sakta hoon.`,
      timestamp: 'Just now',
    },
  ]);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  useEffect(() => {
    if (isOpen) {
      scrollToBottom();
    }
  }, [messages, isOpen]);

  const handleSend = (userQuery?: string) => {
    const q = (userQuery || input).trim();
    if (!q) return;

    const userMsg: Message = {
      id: Date.now().toString(),
      sender: 'user',
      text: q,
      timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    };

    setMessages((prev) => [...prev, userMsg]);
    if (!userQuery) setInput('');
    setIsTyping(true);

    setTimeout(() => {
      const qLower = q.toLowerCase();
      let replyText = '';
      let actionObj: { label: string; onClick: () => void } | undefined = undefined;

      // 1. Advertising System Queries (Packages, Pricing, Telegram, Status)
      if (
        qLower.includes('advertis') ||
        qLower.includes('ishtehar') ||
        qLower.includes('banner') ||
        qLower.includes('campaign') ||
        qLower.includes('homepage ad') ||
        qLower.includes('telegram ad') ||
        qLower.includes('ad kitne') ||
        qLower.includes('ad approve') ||
        qLower.includes('ad expire')
      ) {
        // Specific: Check user's own ad status / expiry
        const userAds = currentUser ? advertisements.filter((a) => a.userId === currentUser.id) : [];

        if (qLower.includes('expire') || qLower.includes('meri ad') || qLower.includes('status') || qLower.includes('approve')) {
          if (!currentUser) {
            replyText = `Aap ki advertisement status check karne ke liye please pehle login karein. Login ke baad aap apne Dashboard → Advertising section mein live status dekh saktay hain.`;
          } else if (userAds.length === 0) {
            replyText = `Aap ne abhi tak koi Advertisement campaign submit nahi ki.\n\nAap Dashboard → 📢 Advertising mein ja kar **Create Advertisement** par click kar ke naya campaign shuru kar saktay hain!`;
            actionObj = {
              label: '📢 Create Advertisement',
              onClick: () => {
                onNavigate('dashboard');
                if (onOpenCreateAd) onOpenCreateAd();
                setIsOpen(false);
              },
            };
          } else {
            const adListText = userAds
              .map(
                (ad) =>
                  `• **${ad.title}** (${ad.packageName}):\n` +
                  `  Status: **${ad.status}**\n` +
                  (ad.status === 'ACTIVE'
                    ? `  Valid: ${ad.startAt} to **${ad.endAt}** (Active)\n`
                    : ad.status === 'PENDING'
                    ? `  Notice: Waiting for Admin verification\n`
                    : `  Expired: ${ad.endAt}\n`) +
                  (ad.telegramEnabled
                    ? `  Telegram: ${ad.telegramStatus === 'published' ? `✅ Published (Msg #${ad.telegramMessageId})` : 'Pending broadcast'}\n`
                    : '')
              )
              .join('\n');

            replyText = `📋 **Aap ki Advertisements ki Tafseelat:**\n\n${adListText}\n` +
              `*Ad expire hone par yeh website se automatically hat jayegi aur Telegram broadcast repeat nahi hoga.*`;

            actionObj = {
              label: '📢 View in Dashboard',
              onClick: () => {
                onNavigate('dashboard');
                setIsOpen(false);
              },
            };
          }
        } else {
          // General Pricing / Packages quote directly from database/state
          const activePkgs = advertisingPackages.filter((p) => p.isActive);
          const pkgLines = activePkgs
            .map(
              (p) =>
                `• **${p.name}**: Rs. ${p.price.toLocaleString()} / ${p.durationDays} Days ` +
                `(${p.placements.join(', ')})${p.telegramEnabled ? ' + 📲 Telegram Broadcast' : ''}`
            )
            .join('\n');

          replyText = `📢 **SargodhaMart Official Advertising Packages:**\n\n` +
            `${pkgLines}\n\n` +
            `⭐ **Main Recommended Package:**\n` +
            `**SargodhaMart Business Promotion** (Rs. 2,000 / 15 Days) jis mein Homepage, Category Page, aur official Telegram Channel sponsored post sab shamil hain!\n\n` +
            `ℹ️ **Kaise karein?**\n` +
            `1. Dashboard → 📢 Advertising → Create Advertisement par jayein.\n` +
            `2. Package muntakhib karein aur apna banner + business number darj karein.\n` +
            `3. EasyPaisa / JazzCash payment screenshot submit karein.\n` +
            `4. Admin verification ke foran baad aap ki ad ACTIVE ho jayegi.`;

          actionObj = {
            label: '📢 Book Advertisement Now',
            onClick: () => {
              onNavigate('dashboard');
              if (onOpenCreateAd) onOpenCreateAd();
              setIsOpen(false);
            },
          };
        }
      }
      // 2. Promote Rules & Fee Separation
      else if (
        qLower.includes('promote') ||
        qLower.includes('feature') ||
        qLower.includes('mashhoor') ||
        qLower.includes('top ad') ||
        qLower.includes('badge')
      ) {
        replyText = `📣 **SargodhaMart Promote System Rules:**\n\n` +
          `• **Product Promote Fee:** Rs. ${promoPrice.toLocaleString()} (15 Days duration)\n` +
          `• **Perks:** ⭐ FEATURED badge, homepage placement, priority search listing, and official Telegram broadcast.\n\n` +
          `⚠️ **Important Fee Separation:**\n` +
          `1. **Lifetime Account Activation:** Rs. ${activationFee.toLocaleString()} (One-time, unlocks posting privileges).\n` +
          `2. **Product / Skill Promotion:** Rs. ${promoPrice.toLocaleString()} (Optional 15-day visibility boost).\n\n` +
          `*Note: Promotion requires Admin verification of payment screenshot.*`;

        actionObj = {
          label: '📣 Open Promote Section',
          onClick: () => {
            onNavigate('dashboard');
            if (onOpenPromoteModal) onOpenPromoteModal();
            setIsOpen(false);
          },
        };
      }
      // 2. Activation vs Promotion Comparison
      else if (
        qLower.includes('activation') ||
        qLower.includes('difference') ||
        qLower.includes('farq') ||
        qLower.includes('1000') ||
        qLower.includes('lifetime')
      ) {
        replyText = `⚖️ **Account Activation vs Product Promotion:**\n\n` +
          `1. **Lifetime Seller Activation (Rs. ${activationFee.toLocaleString()}):**\n` +
          `   • Pay once for lifetime.\n` +
          `   • Mandatory before posting products or digital skills.\n` +
          `   • Requires Trx ID & WhatsApp Channel follow proof.\n\n` +
          `2. **Product / Skill Promotion (Rs. ${promoPrice.toLocaleString()}):**\n` +
          `   • Optional add-on for 15 days.\n` +
          `   • Gives your item ⭐ FEATURED status on top of marketplace.\n` +
          `   • Never combined into a single fee.`;

        if (currentUser?.activationStatus !== 'active' && onOpenActivation) {
          actionObj = {
            label: '🛡️ Activate Lifetime Account',
            onClick: () => {
              onOpenActivation();
              setIsOpen(false);
            },
          };
        }
      }
      // 3. Digital Skills & CV
      else if (
        qLower.includes('skill') ||
        qLower.includes('cv') ||
        qLower.includes('designer') ||
        qLower.includes('developer') ||
        qLower.includes('video') ||
        qLower.includes('freelance') ||
        qLower.includes('humar')
      ) {
        replyText = `💼 **Digital Skills & Digital CV Hub:**\n\n` +
          `SargodhaMart par local talent (Web Designers, Video Editors, Developers, Content Writers) apni Digital Profile aur **Online Digital CV** bana saktay hain!\n\n` +
          `• Showcase your skills, past portfolio & services\n` +
          `• Upload verified PDF CV\n` +
          `• Promote your profile with ⭐ FEATURED badge\n` +
          `• Direct Call & WhatsApp client contacts.`;

        actionObj = {
          label: '💼 Explore Digital Skills',
          onClick: () => {
            onNavigate('skills');
            setIsOpen(false);
          },
        };
      }
      // 4. Location Hierarchy
      else if (
        qLower.includes('location') ||
        qLower.includes('shehr') ||
        qLower.includes('area') ||
        qLower.includes('tehsil') ||
        qLower.includes('sargodha') ||
        qLower.includes('shaheenabad') ||
        qLower.includes('sillanwali')
      ) {
        replyText = `📍 **Sargodha Location Hierarchy:**\n\n` +
          `SargodhaMart par 4-level location filtering dastiab hai:\n` +
          `1. **Division:** Sargodha Division\n` +
          `2. **District:** Sargodha District\n` +
          `3. **Tehsils:** Sargodha Tehsil, Sillanwali, Shaheenabad, Sahiwal, Bhalwal, Kot Momin\n` +
          `4. **Local Areas:** Satellite Town, University Road, Main Citrus Mandi, Canal Colony, Kutchery Bazaar.\n\n` +
          `Aap Products, Jobs aur Digital Skills ko tehsil aur area ke hisab se filter kar saktay hain.`;

        actionObj = {
          label: '🔍 Filter in Marketplace',
          onClick: () => {
            onNavigate('home');
            setIsOpen(false);
          },
        };
      }
      // 5. Navigation & How to use
      else if (
        qLower.includes('navigate') ||
        qLower.includes('kahan') ||
        qLower.includes('rasta') ||
        qLower.includes('menu') ||
        qLower.includes('help')
      ) {
        replyText = `🧭 **SargodhaMart Quick Navigation:**\n\n` +
          `• **Marketplace:** Browse & buy local products\n` +
          `• **Jobs Hub:** I Need a Job / I Need a Worker\n` +
          `• **Digital Skills:** Freelancers & local tech talents\n` +
          `• **Dashboard:** Manage ads, view favorites & promote items\n` +
          `• **Admin Panel:** Administrative moderation & logs.`;

        actionObj = {
          label: '🛒 Go to Marketplace',
          onClick: () => {
            onNavigate('home');
            setIsOpen(false);
          },
        };
      }
      // Default / General
      else {
        replyText = `Main aapki madad ke liye hazir hoon! Aap mujh se yeh pooch saktay hain:\n\n` +
          `1. **Promote Rules:** Product ya Skill ko 15 din ke liye feature kaise karein?\n` +
          `2. **Fee Separation:** Lifetime activation (Rs. 1,000) aur Promotion (Rs. 1,000) mein kya farq hai?\n` +
          `3. **Digital Skills:** Digital CV kaise banayein?\n` +
          `4. **Location:** Tehsil ya Area filter kaise use karein?`;
      }

      setMessages((prev) => [
        ...prev,
        {
          id: (Date.now() + 1).toString(),
          sender: 'bot',
          text: replyText,
          timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
          action: actionObj,
        },
      ]);
      setIsTyping(false);
    }, 450);
  };

  return (
    <>
      {/* Persistent Floating AI Launcher Button - Bottom Right */}
      <div className="fixed bottom-5 right-5 z-50 flex flex-col items-end">
        {!isOpen && (
          <button
            onClick={() => setIsOpen(true)}
            aria-label="Open SargodhaMart AI Assistant"
            className="group relative flex items-center gap-2.5 px-4 py-3 rounded-full bg-gradient-to-r from-slate-950 via-slate-900 to-cyan-950 text-white border-2 border-cyan-400/80 shadow-[0_10px_35px_rgba(6,182,212,0.45)] hover:shadow-[0_15px_45px_rgba(6,182,212,0.65)] hover:scale-105 transition-all duration-300 active:scale-95"
          >
            {/* Glowing ring animation */}
            <span className="absolute -inset-0.5 rounded-full bg-gradient-to-r from-cyan-500 via-blue-500 to-fuchsia-500 opacity-60 blur-xs group-hover:opacity-100 transition duration-300 animate-pulse" />
            
            <div className="relative flex items-center justify-center w-8 h-8 rounded-full bg-gradient-to-tr from-cyan-500 to-blue-600 text-white shadow-inner">
              <Bot className="w-5 h-5 animate-wiggle" />
            </div>

            <div className="relative text-left hidden sm:block">
              <div className="text-[11px] font-black uppercase tracking-wider text-cyan-300 flex items-center gap-1">
                AI Assistant
                <span className="w-2 h-2 rounded-full bg-emerald-400 animate-ping inline-block" />
              </div>
              <div className="text-xs font-bold text-white leading-tight">Ask SargodhaMart</div>
            </div>

            <span className="relative sm:hidden text-xs font-bold text-cyan-300">AI Help</span>
          </button>
        )}

        {/* Floating Chat Modal Window */}
        {isOpen && (
          <div
            className="w-[92vw] sm:w-[410px] h-[550px] max-h-[85vh] flex flex-col rounded-3xl bg-slate-950/95 backdrop-blur-2xl border-2 border-cyan-400/70 shadow-[0_20px_60px_rgba(0,0,0,0.85),0_0_40px_rgba(6,182,212,0.3)] overflow-hidden animate-in fade-in slide-in-from-bottom-6 duration-300"
          >
            {/* Modal Header */}
            <div className="p-4 bg-gradient-to-r from-slate-900 via-cyan-950 to-slate-900 border-b border-cyan-500/30 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="relative w-10 h-10 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/30">
                  <Bot className="w-6 h-6" />
                  <span className="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-500 border-2 border-slate-900" />
                </div>
                <div>
                  <h3 className="text-sm font-black text-white flex items-center gap-1.5">
                    SargodhaMart AI Assistant
                    <span className="text-[9px] px-1.5 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 font-extrabold border border-cyan-500/40">
                      LIVE
                    </span>
                  </h3>
                  <p className="text-[11px] text-cyan-200/80 font-medium">
                    Guidance • Promotion Rules • Navigation
                  </p>
                </div>
              </div>
              <button
                onClick={() => setIsOpen(false)}
                className="w-8 h-8 rounded-full bg-slate-800/80 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition-colors border border-slate-700"
                aria-label="Close Assistant"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            {/* Quick Prompt Pill Strip */}
            <div className="px-3 py-2 bg-slate-900/90 border-b border-slate-800 flex items-center gap-1.5 overflow-x-auto scrollbar-none text-[11px]">
              <button
                onClick={() => handleSend('What is the Promote System and how does it work?')}
                className="whitespace-nowrap px-2.5 py-1 rounded-full bg-cyan-950/80 text-cyan-200 border border-cyan-800 hover:border-cyan-400 hover:bg-cyan-900/60 transition-all font-semibold"
              >
                📣 Promote System
              </button>
              <button
                onClick={() => handleSend('Difference between Rs. 1,000 Activation and Promotion?')}
                className="whitespace-nowrap px-2.5 py-1 rounded-full bg-blue-950/80 text-blue-200 border border-blue-800 hover:border-blue-400 hover:bg-blue-900/60 transition-all font-semibold"
              >
                ⭐ Activation vs Promotion
              </button>
              <button
                onClick={() => handleSend('How do Digital Skills and Digital CV work?')}
                className="whitespace-nowrap px-2.5 py-1 rounded-full bg-fuchsia-950/80 text-fuchsia-200 border border-fuchsia-800 hover:border-fuchsia-400 hover:bg-fuchsia-900/60 transition-all font-semibold"
              >
                💼 Digital CV
              </button>
              <button
                onClick={() => handleSend('Show me Location hierarchy for Sargodha Division')}
                className="whitespace-nowrap px-2.5 py-1 rounded-full bg-emerald-950/80 text-emerald-200 border border-emerald-800 hover:border-emerald-400 hover:bg-emerald-900/60 transition-all font-semibold"
              >
                📍 Locations
              </button>
            </div>

            {/* Chat Body */}
            <div className="flex-1 p-4 overflow-y-auto space-y-3 scrollbar-thin scrollbar-thumb-slate-700">
              {messages.map((m) => (
                <div
                  key={m.id}
                  className={`flex ${m.sender === 'user' ? 'justify-end' : 'justify-start'}`}
                >
                  <div
                    className={`max-w-[85%] rounded-2xl p-3 text-xs leading-relaxed ${
                      m.sender === 'user'
                        ? 'bg-gradient-to-r from-cyan-600 to-blue-600 text-white rounded-tr-none shadow-md shadow-cyan-600/20'
                        : 'bg-slate-900 border border-cyan-500/20 text-slate-200 rounded-tl-none shadow-md shadow-black/40'
                    }`}
                  >
                    <div className="whitespace-pre-line">{m.text}</div>
                    
                    {m.action && (
                      <div className="mt-3 pt-2.5 border-t border-cyan-500/30">
                        <button
                          onClick={m.action.onClick}
                          className="w-full py-1.5 px-3 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-black text-[11px] shadow-md shadow-cyan-500/30 flex items-center justify-center gap-1.5 transition-all"
                        >
                          {m.action.label}
                          <ArrowRight className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    )}

                    <div
                      className={`text-[9px] mt-1 font-mono ${
                        m.sender === 'user' ? 'text-cyan-200 text-right' : 'text-slate-500'
                      }`}
                    >
                      {m.timestamp}
                    </div>
                  </div>
                </div>
              ))}

              {isTyping && (
                <div className="flex justify-start">
                  <div className="bg-slate-900 border border-cyan-500/20 rounded-2xl rounded-tl-none p-3 text-xs text-cyan-300 flex items-center gap-1.5">
                    <span className="w-2 h-2 rounded-full bg-cyan-400 animate-bounce" />
                    <span className="w-2 h-2 rounded-full bg-cyan-400 animate-bounce [animation-delay:0.2s]" />
                    <span className="w-2 h-2 rounded-full bg-cyan-400 animate-bounce [animation-delay:0.4s]" />
                    <span className="text-[10px] text-slate-400 ml-1">Analyzing platform knowledge...</span>
                  </div>
                </div>
              )}
              <div ref={messagesEndRef} />
            </div>

            {/* Input Footer */}
            <form
              onSubmit={(e) => {
                e.preventDefault();
                handleSend();
              }}
              className="p-3 bg-slate-900 border-t border-slate-800 flex items-center gap-2"
            >
              <input
                type="text"
                value={input}
                onChange={(e) => setInput(e.target.value)}
                placeholder="Ask in Urdu / Roman Urdu / English..."
                className="flex-1 px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 text-xs focus:outline-hidden focus:border-cyan-400 transition-colors"
              />
              <button
                type="submit"
                disabled={!input.trim()}
                className="p-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-md shadow-cyan-500/20 disabled:opacity-40 disabled:cursor-not-allowed transition-all"
                aria-label="Send query"
              >
                <Send className="w-4 h-4" />
              </button>
            </form>
          </div>
        )}
      </div>
    </>
  );
};
