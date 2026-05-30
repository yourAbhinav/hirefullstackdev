import React, { useState } from 'react';
import { LogIn, Key, Mail, ShieldAlert, Sparkles, CheckCircle } from 'lucide-react';

interface LoginPageProps {
  onLoginSuccess: (email: string, role: 'developer' | 'company' | 'admin') => void;
  onNavigate: (page: string) => void;
}

export const LoginPage: React.FC<LoginPageProps> = ({ onLoginSuccess, onNavigate }) => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    if (!email || !password) {
      setError('Please provide both your registered email and password.');
      return;
    }

    setLoading(true);

    // Simulated Authentication matching database separation guidelines:
    // admin@devhire.com -> admin role
    // abhinavkumark70@gmail.com -> developer role
    // info@stripe.com -> company role
    setTimeout(() => {
      setLoading(false);
      
      const emailLower = email.toLowerCase();
      if (emailLower === 'admin@devhire.com' && password === 'admin123') {
        onLoginSuccess('admin@devhire.com', 'admin');
        onNavigate('admin');
      } else if (emailLower.includes('company') || emailLower.includes('stripe') || emailLower === 'info@stripe.com') {
        onLoginSuccess(email, 'company');
        onNavigate('company');
      } else {
        // Default developer login simulation
        onLoginSuccess(email, 'developer');
        onNavigate('developers');
      }
    }, 800);
  };

  return (
    <div className="relative py-16 sm:py-24 overflow-hidden bg-slate-950 text-white min-h-[90vh] flex items-center">
      <div className="absolute inset-0 bg-grid-ambient opacity-50" />
      <div className="absolute top-20 left-1/3 h-[400px] w-[500px] rounded-full bg-brand-primary/10 blur-[130px] pointer-events-none" />

      <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 w-full">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center max-w-5xl mx-auto">
          
          {/* LEFT: Branding highlight column */}
          <div className="lg:col-span-7 space-y-6 hidden lg:block">
            <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-secondary/10 border border-brand-secondary/25 text-xs text-brand-secondary font-mono tracking-wider uppercase mb-2">
              <Sparkles className="h-3.5 w-3.5" />
              <span>Unified Auth Gate</span>
            </div>
            
            <h1 className="font-display text-4xl font-extrabold tracking-tight leading-tight">
              Single-Sign On <br />
              Secure Developer Sessions
            </h1>
            <p className="text-slate-400 text-sm max-w-md leading-relaxed">
              Log in to access certified developer benchmarks, post job requirements, and view active candidate application panels.
            </p>

            <ul className="space-y-3 pt-4">
              <li className="flex items-center gap-2.5 text-xs text-slate-300">
                <CheckCircle className="h-4.5 w-4.5 text-brand-secondary" />
                <span>Encrypted bcrypt session tokens</span>
              </li>
              <li className="flex items-center gap-2.5 text-xs text-slate-300">
                <CheckCircle className="h-4.5 w-4.5 text-brand-secondary" />
                <span>Automatic role separation and dashboard triggers</span>
              </li>
              <li className="flex items-center gap-2.5 text-xs text-slate-300">
                <CheckCircle className="h-4.5 w-4.5 text-brand-secondary" />
                <span>CSRF protection headers active</span>
              </li>
            </ul>
          </div>

          {/* RIGHT: Login Card - 5cols */}
          <div className="lg:col-span-5 w-full max-w-md mx-auto">
            <div className="rounded-3xl border border-slate-900 bg-slate-900/20 p-8 glass-panel shadow-2xl">
              
              <div className="text-center mb-6">
                <h2 className="font-display font-extrabold text-2xl text-white">Login Account</h2>
                <p className="text-xs text-slate-500 mt-1">Provide credential keys below</p>
              </div>

              <form onSubmit={handleSubmit} className="space-y-5">
                
                {error && (
                  <div className="flex items-center gap-2.5 p-3.5 rounded-xl bg-rose-500/5 border border-rose-500/20 text-xs text-brand-accent">
                    <ShieldAlert className="h-4 w-4" />
                    <span>{error}</span>
                  </div>
                )}

                <div className="space-y-1.5">
                  <label htmlFor="email" className="text-xs font-semibold text-slate-400 tracking-wider uppercase flex items-center gap-1.5">
                    <Mail className="h-3.5 w-3.5" />
                    <span>Email Address</span>
                  </label>
                  <input
                    type="email"
                    id="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="w-full h-11 px-3.5 rounded-xl border border-slate-800 bg-slate-1000/60 text-xs text-white placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-brand-primary"
                    placeholder="e.g. admin@devhire.com"
                  />
                </div>

                <div className="space-y-1.5">
                  <div className="flex items-center justify-between">
                    <label htmlFor="password" className="text-xs font-semibold text-slate-400 tracking-wider uppercase flex items-center gap-1.5">
                      <Key className="h-3.5 w-3.5" />
                      <span>Password Key</span>
                    </label>
                    <span className="text-[10px] text-slate-500 hover:text-slate-300 transition-all cursor-pointer">
                      Forgot?
                    </span>
                  </div>
                  <input
                    type="password"
                    id="password"
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    className="w-full h-11 px-3.5 rounded-xl border border-slate-800 bg-slate-1000/60 text-xs text-white placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-brand-primary"
                    placeholder="Password input keys..."
                  />
                </div>

                <div className="pt-2">
                  <button
                    type="submit"
                    disabled={loading}
                    className="w-full h-11 rounded-xl bg-gradient-to-r from-brand-primary to-indigo-500 text-xs font-semibold hover:opacity-95 shadow-lg shadow-brand-primary/20 transition-all cursor-pointer flex items-center justify-center gap-2"
                  >
                    <LogIn className="h-4 w-4" />
                    <span>{loading ? 'Verifying S.S.O Key...' : 'Sign In To Account'}</span>
                  </button>
                </div>

              </form>

              <div className="h-px bg-slate-900 my-6" />

              {/* Quick instructions indicator details */}
              <div className="text-[11px] text-slate-500 bg-slate-950/80 rounded-xl border border-slate-900 p-3 mb-6 space-y-1">
                <p className="font-bold text-slate-400">TEST LOGIN DETAILS:</p>
                <p>• Admin: <code className="text-slate-300 font-mono">admin@devhire.com</code> / <code className="text-slate-300 font-mono">admin123</code></p>
                <p>• Company: <code className="text-slate-300 font-mono">info@stripe.com</code> (any company email)</p>
                <p>• Developer: <code className="text-slate-300 font-mono">any email</code></p>
              </div>

              <div className="text-center text-xs text-slate-500">
                <span>New Sourcing Client? </span>
                <button 
                  onClick={() => onNavigate('register')}
                  className="text-brand-secondary font-semibold hover:underline cursor-pointer"
                >
                  Create Secure Account
                </button>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>
  );
};
