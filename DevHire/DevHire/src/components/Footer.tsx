import React from 'react';
import { SITE_CONFIG } from '../config';
import { Mail, Phone, MapPin, Shield, CheckCircle } from 'lucide-react';

interface FooterProps {
  onNavigate: (page: string) => void;
}

export const Footer: React.FC<FooterProps> = ({ onNavigate }) => {
  return (
    <footer className="relative z-10 border-t border-slate-900 bg-slate-950 text-slate-400">
      <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 gap-10 md:grid-cols-4 lg:gap-12">
          
          {/* Brand Col */}
          <div className="space-y-4">
            <div className="flex items-center gap-2">
              <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-tr from-brand-primary to-brand-secondary text-white font-display font-black text-sm">
                DH
              </div>
              <span className="font-display font-bold text-white tracking-tight">{SITE_CONFIG.companyName}</span>
            </div>
            <p className="text-sm leading-relaxed text-slate-500">
              {SITE_CONFIG.description}
            </p>
            <div className="flex items-center gap-1 text-xs text-emerald-400/80 bg-emerald-500/5 px-2 py-1 rounded w-fit border border-emerald-500/10">
              <Shield className="h-3 w-3" />
              <span>SOC2 Certified • Secure Storage</span>
            </div>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="text-sm font-semibold text-white tracking-wider uppercase mb-4">Platform</h3>
            <ul className="space-y-2.5 text-sm">
              <li>
                <button onClick={() => onNavigate('jobs')} className="hover:text-white transition-all cursor-pointer">
                  Explore Jobs
                </button>
              </li>
              <li>
                <button onClick={() => onNavigate('developers')} className="hover:text-white transition-all cursor-pointer">
                  Browse Developers
                </button>
              </li>
              <li>
                <button onClick={() => onNavigate('how-it-works')} className="hover:text-white transition-all cursor-pointer">
                  How It Works
                </button>
              </li>
              <li>
                <button onClick={() => onNavigate('pricing')} className="hover:text-white transition-all cursor-pointer">
                  Plan & Pricing
                </button>
              </li>
            </ul>
          </div>

          {/* Guidelines / Privacy */}
          <div>
            <h3 className="text-sm font-semibold text-white tracking-wider uppercase mb-4">Privacy & Terms</h3>
            <ul className="space-y-2.5 text-sm">
              <li>
                <button onClick={() => onNavigate('privacy')} className="hover:text-white transition-all cursor-pointer text-slate-500 hover:text-slate-300">
                  Privacy Policy
                </button>
              </li>
              <li>
                <button onClick={() => onNavigate('terms')} className="hover:text-white transition-all cursor-pointer text-slate-500 hover:text-slate-300">
                  Terms of Service
                </button>
              </li>
              <li>
                <button onClick={() => onNavigate('cookies')} className="hover:text-white transition-all cursor-pointer text-slate-500 hover:text-slate-300">
                  Cookie Policy
                </button>
              </li>
              <li>
                <div className="flex items-center gap-1.5 text-slate-500 text-xs pt-1.5">
                  <CheckCircle className="h-3.5 w-3.5 text-brand-secondary" />
                  <span>Terms acceptance enforced</span>
                </div>
              </li>
            </ul>
          </div>

          {/* Contact Col */}
          <div>
            <h3 className="text-sm font-semibold text-white tracking-wider uppercase mb-4">Contact Desk</h3>
            <ul className="space-y-3 text-sm text-slate-500">
              <li className="flex items-start gap-2.5">
                <MapPin className="h-4 w-4 text-brand-primary flex-shrink-0 mt-0.5" />
                <span className="text-slate-400">{SITE_CONFIG.contact.address}</span>
              </li>
              <li className="flex items-center gap-2.5">
                <Phone className="h-4 w-4 text-brand-secondary flex-shrink-0" />
                <span className="text-slate-400">{SITE_CONFIG.contact.phone}</span>
              </li>
              <li className="flex items-center gap-2.5">
                <Mail className="h-4 w-4 text-brand-secondary flex-shrink-0" />
                <a href={`mailto:${SITE_CONFIG.contact.email}`} className="text-slate-400 hover:text-white transition-all">
                  {SITE_CONFIG.contact.email}
                </a>
              </li>
              <li className="text-[11px] font-mono pt-1 text-slate-600 block">
                {SITE_CONFIG.contact.hours}
              </li>
            </ul>
          </div>

        </div>

        <div className="mt-12 border-t border-slate-900 pt-6 text-center text-xs text-slate-600 flex flex-col sm:flex-row items-center justify-between gap-4">
          <p>© {new Date().getFullYear()} {SITE_CONFIG.companyName}. All rights reserved.</p>
          <div className="flex items-center gap-4 text-slate-600">
            <a href={SITE_CONFIG.social.github} target="_blank" rel="noreferrer" className="hover:text-white transition-all">GitHub</a>
            <span>•</span>
            <a href={SITE_CONFIG.social.linkedin} target="_blank" rel="noreferrer" className="hover:text-white transition-all">LinkedIn</a>
            <span>•</span>
            <a href={SITE_CONFIG.social.twitter} target="_blank" rel="noreferrer" className="hover:text-white transition-all">Twitter</a>
          </div>
        </div>

      </div>
    </footer>
  );
};
