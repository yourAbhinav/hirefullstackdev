import React from 'react';
import { Check, ShieldCheck, HelpCircle, Star, Sparkles } from 'lucide-react';

interface PricingProps {
  onNavigate: (page: string) => void;
}

export const PricingPage: React.FC<PricingProps> = ({ onNavigate }) => {
  const plans = [
    {
      name: 'Starter Sourcing',
      price: '$199',
      period: 'per month',
      desc: 'Perfect for fast-growing startups looking to hire 1-2 core developers with basic verification checking tools.',
      features: [
        'Access to full verified catalog',
        'Direct connection to 3 applicants/mo',
        'Basic candidate chat messenger',
        'Standard customer care desk',
        'Candidate background verification'
      ],
      popular: false,
      cta: 'Start Sourcing'
    },
    {
      name: 'Global Professional',
      price: '$499',
      period: 'per month',
      desc: 'Our most popular plan. Integrated sourcing pipeline with dedicated filters, certified tech challenges, and direct contract modules.',
      features: [
        'Unlimited candidate search & filtering',
        'Infinite candidate connections',
        'Priority verified badge vetting results',
        'Custom interactive stack test checks',
        'Dedicated account manager assistance',
        'Secure escrow payment channels',
        'Advanced audit admin report access'
      ],
      popular: true,
      cta: 'Go Professional'
    },
    {
      name: 'Enterprise Scaling',
      price: 'Custom',
      period: 'tailored setup',
      desc: 'Designed for high-throughput enterprises requiring bulk sourcing, custom coding testing adapters, and co-branded dashboards.',
      features: [
        'Co-branded company candidate panels',
        'Tailored MySQL/Node challenge testing',
        'Premium vetting & active screening calls',
        'Direct payroll integration APIs',
        'Special legal SLA contracts setup',
        'Dedicated 24/7 priority support'
      ],
      popular: false,
      cta: 'Contact Sourcing Office'
    }
  ];

  return (
    <div className="relative py-16 sm:py-24 overflow-hidden bg-slate-950 text-white">
      {/* Background patterns */}
      <div className="absolute inset-0 bg-grid-ambient opacity-50" />
      <div className="absolute top-40 right-10 h-[300px] w-[500px] rounded-full bg-brand-secondary/5 blur-[120px] pointer-events-none" />

      <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        {/* Title */}
        <div className="text-center max-w-2xl mx-auto mb-16">
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-secondary/10 border border-brand-secondary/25 text-xs text-brand-secondary font-mono tracking-wider uppercase mb-4">
            <Sparkles className="h-3 w-3" />
            <span>Guaranteed Credentials</span>
          </div>
          <h1 className="font-display text-4xl sm:text-5xl font-extrabold tracking-tight mb-4">
            Predictable, Flat Sourcing Planes
          </h1>
          <p className="text-base text-slate-400">
            Choose a plan matching your scale. We do not charge high recruitment fee cuts. Select a flat, reliable monthly membership.
          </p>
        </div>

        {/* Pricing Cards */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch mb-20">
          {plans.map((p) => (
            <div
              key={p.name}
              className={`relative rounded-3xl p-8 flex flex-col justify-between glass-panel transition-all duration-300 ${
                p.popular 
                  ? 'border-brand-primary bg-slate-900/60 ring-1 ring-brand-primary/45 shadow-2xl shadow-brand-primary/10 scale-100 lg:scale-[1.03]' 
                  : 'bg-slate-900/30'
              }`}
            >
              {p.popular && (
                <span className="absolute -top-3.5 left-1/2 -translate-x-1/2 rounded-full bg-gradient-to-r from-brand-primary to-indigo-500 text-[10px] font-bold uppercase tracking-widest text-white px-3 py-1 flex items-center gap-1">
                  <Star className="h-2.5 w-2.5 fill-white" />
                  Most Popular
                </span>
              )}

              <div>
                <span className="text-xs font-mono tracking-widest text-slate-500 uppercase block mb-1">
                  {p.name}
                </span>
                
                <div className="flex items-baseline gap-2 mb-4">
                  <span className="text-4xl sm:text-5xl font-display font-black text-white">
                    {p.price}
                  </span>
                  <span className="text-xs text-slate-500 font-medium">
                    {p.period}
                  </span>
                </div>

                <p className="text-sm text-slate-400 leading-relaxed mb-8">
                  {p.desc}
                </p>

                <div className="h-px bg-slate-900 mb-8" />

                <h4 className="text-xs font-semibold text-slate-300 uppercase tracking-widest mb-4">
                  What is included:
                </h4>

                <ul className="space-y-3 mb-8">
                  {p.features.map((f, i) => (
                    <li key={i} className="flex items-start gap-2.5 text-sm">
                      <div className="flex h-5 w-5 items-center justify-center rounded-full bg-slate-950 border border-slate-800 text-brand-secondary flex-shrink-0 mt-0.5">
                        <Check className="h-3 w-3" />
                      </div>
                      <span className="text-slate-300">{f}</span>
                    </li>
                  ))}
                </ul>
              </div>

              <button
                onClick={() => onNavigate('contact')}
                className={`w-full py-3.5 rounded-xl text-center text-sm font-semibold transition-all cursor-pointer ${
                  p.popular
                    ? 'bg-gradient-to-r from-brand-primary to-indigo-500 hover:opacity-95 text-white shadow-lg shadow-brand-primary/20'
                    : 'bg-slate-950 border border-slate-800 hover:bg-slate-900 text-slate-300'
                }`}
              >
                {p.cta}
              </button>
            </div>
          ))}
        </div>

        {/* FAQ note */}
        <div className="max-w-3xl mx-auto rounded-3xl p-6 sm:p-8 bg-slate-900/10 border border-slate-900/60 text-center flex flex-col sm:flex-row items-center gap-6 justify-between glass-panel">
          <div className="text-left">
            <h4 className="text-base font-semibold text-white flex items-center gap-2">
              <ShieldCheck className="h-5 w-5 text-brand-secondary" />
              <span>Sourcing Safe Guarantee</span>
            </h4>
            <p className="text-xs text-slate-400 mt-1">
              Test drive for 14 days under standard contract options. Cancel anytime with a single click. No question-asking audits.
            </p>
          </div>
          <button
            onClick={() => onNavigate('contact')}
            className="px-5 py-2.5 rounded-xl bg-slate-900 text-xs font-medium border border-slate-800 text-slate-300 hover:text-white hover:bg-slate-800 transition-all cursor-pointer flex-shrink-0"
          >
            Connect Sourcing Office
          </button>
        </div>

      </div>
    </div>
  );
};
