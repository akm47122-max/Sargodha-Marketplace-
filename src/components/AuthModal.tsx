import React, { useState } from 'react';
import { X, UserPlus, LogIn, ShieldCheck, MapPin, Phone, Mail, Lock, User, ArrowRight } from 'lucide-react';
import { City, UserProfile } from '../types';

interface AuthModalProps {
  isOpen: boolean;
  onClose: () => void;
  onLoginSuccess: (user: UserProfile) => void;
  onRegisterSuccess: (newUser: UserProfile) => void;
  existingUsers: UserProfile[];
}

export const AuthModal: React.FC<AuthModalProps> = ({
  isOpen,
  onClose,
  onLoginSuccess,
  onRegisterSuccess,
  existingUsers,
}) => {
  const [authMode, setAuthMode] = useState<'register' | 'login'>('register');

  // Register Fields
  const [name, setName] = useState('');
  const [mobile, setMobile] = useState('');
  const [email, setEmail] = useState('');
  const [city, setCity] = useState<City>('Sargodha');
  const [area, setArea] = useState('');
  const [password, setPassword] = useState('');

  // Login Fields
  const [loginIdentifier, setLoginIdentifier] = useState('');
  const [loginPassword, setLoginPassword] = useState('');

  const [error, setError] = useState('');

  if (!isOpen) return null;

  const handleRegister = (e: React.FormEvent) => {
    e.preventDefault();
    if (!name.trim() || !mobile.trim() || !area.trim() || !password.trim()) {
      setError('Please fill out all required fields.');
      return;
    }

    const newUser: UserProfile = {
      id: Date.now(),
      name: name.trim(),
      mobile: mobile.trim(),
      email: email.trim() || `${mobile.trim()}@sargodhamart.com`,
      city,
      area: area.trim(),
      role: 'user',
      activationStatus: 'pending',
      joinedDate: 'Just now',
    };

    onRegisterSuccess(newUser);
    onClose();
  };

  const handleLogin = (e: React.FormEvent) => {
    e.preventDefault();
    if (!loginIdentifier.trim()) {
      setError('Please enter your mobile number or email.');
      return;
    }

    const matched = existingUsers.find(
      (u) =>
        u.mobile.toLowerCase() === loginIdentifier.trim().toLowerCase() ||
        u.email.toLowerCase() === loginIdentifier.trim().toLowerCase()
    );

    if (matched) {
      onLoginSuccess(matched);
      onClose();
    } else {
      const guestUser: UserProfile = {
        id: Date.now(),
        name: loginIdentifier.includes('@') ? loginIdentifier.split('@')[0] : `Seller ${loginIdentifier.slice(-4)}`,
        mobile: loginIdentifier.startsWith('03') ? loginIdentifier : '03001234567',
        email: loginIdentifier.includes('@') ? loginIdentifier : `${loginIdentifier}@sargodhamart.com`,
        city: 'Sargodha',
        area: 'Satellite Town',
        role: 'user',
        activationStatus: 'pending',
        joinedDate: 'Today',
      };
      onLoginSuccess(guestUser);
      onClose();
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-md overflow-y-auto">
      <div className="relative w-full max-w-md bg-white border border-cyan-200 rounded-3xl p-6 md:p-8 shadow-[0_20px_60px_-15px_rgba(6,182,212,0.25)] my-8">
        <button
          onClick={onClose}
          className="absolute top-5 right-5 p-2 rounded-xl text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-all"
        >
          <X className="w-5 h-5" />
        </button>

        {/* Tab switchers: Register vs Login */}
        <div className="flex bg-slate-100 p-1.5 rounded-2xl border border-slate-200 mb-6">
          <button
            onClick={() => { setAuthMode('register'); setError(''); }}
            className={`flex-1 py-2 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-1.5 ${
              authMode === 'register'
                ? 'bg-white text-cyan-800 shadow-[0_0_15px_rgba(6,182,212,0.2)] border border-cyan-300'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            <UserPlus className="w-4 h-4 text-cyan-600" />
            <span>Register to Post</span>
          </button>
          <button
            onClick={() => { setAuthMode('login'); setError(''); }}
            className={`flex-1 py-2 rounded-xl font-bold text-xs transition-all flex items-center justify-center gap-1.5 ${
              authMode === 'login'
                ? 'bg-white text-cyan-800 shadow-[0_0_15px_rgba(6,182,212,0.2)] border border-cyan-300'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            <LogIn className="w-4 h-4 text-cyan-600" />
            <span>Login to Account</span>
          </button>
        </div>

        {/* Header Text */}
        <div className="mb-6">
          <h2 className="text-xl font-black text-slate-900">
            {authMode === 'register' ? 'Create SargodhaMart Account' : 'Welcome Back'}
          </h2>
          <p className="text-slate-500 text-xs mt-1 font-medium">
            {authMode === 'register'
              ? 'Public browsing is always free. Register to activate your lifetime seller account.'
              : 'Login with your registered phone number or email to manage ads.'}
          </p>
        </div>

        {error && (
          <div className="p-3 rounded-xl bg-rose-50 border border-rose-300 text-rose-700 text-xs mb-4 font-semibold">
            {error}
          </div>
        )}

        {/* REGISTER FORM */}
        {authMode === 'register' && (
          <form onSubmit={handleRegister} className="space-y-3.5 text-xs">
            <div>
              <label className="block text-slate-800 font-semibold mb-1">Full Name / Business Name *</label>
              <div className="relative">
                <User className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input
                  type="text"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  placeholder="e.g. Malik Tariq Dairy or Muhammad Ali"
                  className="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-cyan-500 focus:bg-white font-medium"
                  required
                />
              </div>
            </div>

            <div>
              <label className="block text-slate-800 font-semibold mb-1">Mobile Phone (WhatsApp Active) *</label>
              <div className="relative">
                <Phone className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input
                  type="text"
                  value={mobile}
                  onChange={(e) => setMobile(e.target.value)}
                  placeholder="03001234567"
                  className="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-cyan-500 focus:bg-white font-medium"
                  required
                />
              </div>
            </div>

            <div className="grid grid-cols-2 gap-3">
              <div>
                <label className="block text-slate-800 font-semibold mb-1">City / Tehsil *</label>
                <select
                  value={city}
                  onChange={(e) => setCity(e.target.value as City)}
                  className="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-cyan-500 focus:bg-white font-medium"
                >
                  <option value="Sargodha">Sargodha</option>
                  <option value="Shaheenabad">Shaheenabad</option>
                  <option value="Sillanwali">Sillanwali</option>
                </select>
              </div>

              <div>
                <label className="block text-slate-800 font-semibold mb-1">Area / Mohalla *</label>
                <input
                  type="text"
                  value={area}
                  onChange={(e) => setArea(e.target.value)}
                  placeholder="e.g. Satellite Town"
                  className="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-cyan-500 focus:bg-white font-medium"
                  required
                />
              </div>
            </div>

            <div>
              <label className="block text-slate-800 font-semibold mb-1">Email Address (Optional)</label>
              <div className="relative">
                <Mail className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="name@gmail.com"
                  className="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-cyan-500 focus:bg-white font-medium"
                />
              </div>
            </div>

            <div>
              <label className="block text-slate-800 font-semibold mb-1">Create Password *</label>
              <div className="relative">
                <Lock className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="••••••••"
                  className="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-cyan-500 focus:bg-white font-medium"
                  required
                />
              </div>
            </div>

            <div className="p-3 rounded-xl bg-cyan-50/70 border border-cyan-200 text-[11px] text-slate-600 font-medium">
              <strong className="text-cyan-800">Next Step:</strong> After free registration, you will be prompted to activate your lifetime seller account (Rs. 1,000 once) & follow the official WhatsApp Channel to unlock unlimited direct public postings.
            </div>

            <button
              type="submit"
              className="w-full py-3 rounded-xl font-bold bg-gradient-to-r from-cyan-500 via-blue-600 to-fuchsia-600 hover:from-cyan-400 hover:via-blue-500 hover:to-fuchsia-500 text-white shadow-lg shadow-cyan-500/25 transition-all flex items-center justify-center gap-2 hover:shadow-[0_0_15px_rgba(6,182,212,0.4)]"
            >
              <span>Continue to Activation</span>
              <ArrowRight className="w-4 h-4" />
            </button>
          </form>
        )}

        {/* LOGIN FORM */}
        {authMode === 'login' && (
          <form onSubmit={handleLogin} className="space-y-4 text-xs">
            <div>
              <label className="block text-slate-800 font-semibold mb-1">Mobile Number or Email *</label>
              <div className="relative">
                <Phone className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input
                  type="text"
                  value={loginIdentifier}
                  onChange={(e) => setLoginIdentifier(e.target.value)}
                  placeholder="03127453108 or admin@sargodhamart.com"
                  className="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-cyan-500 focus:bg-white font-medium"
                  required
                />
              </div>
            </div>

            <div>
              <label className="block text-slate-800 font-semibold mb-1">Password *</label>
              <div className="relative">
                <Lock className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                <input
                  type="password"
                  value={loginPassword}
                  onChange={(e) => setLoginPassword(e.target.value)}
                  placeholder="••••••••"
                  className="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:border-cyan-500 focus:bg-white font-medium"
                  required
                />
              </div>
            </div>

            <div className="text-[11px] text-slate-500 font-medium">
              Quick test accounts:
              <div className="flex flex-wrap gap-1.5 mt-1">
                {existingUsers.slice(0, 3).map((u) => (
                  <button
                    key={u.id}
                    type="button"
                    onClick={() => {
                      setLoginIdentifier(u.mobile);
                      setLoginPassword('123456');
                    }}
                    className="px-2 py-0.5 rounded-lg bg-slate-100 text-[10px] text-cyan-800 font-bold hover:bg-slate-200 border border-slate-200"
                  >
                    {u.name.split(' ')[0]} ({u.activationStatus})
                  </button>
                ))}
              </div>
            </div>

            <button
              type="submit"
              className="w-full py-3 rounded-xl font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-lg shadow-cyan-500/25 transition-all flex items-center justify-center gap-2 hover:shadow-[0_0_15px_rgba(6,182,212,0.4)]"
            >
              <LogIn className="w-4 h-4" />
              <span>Login & Open Dashboard</span>
            </button>
          </form>
        )}
      </div>
    </div>
  );
};
