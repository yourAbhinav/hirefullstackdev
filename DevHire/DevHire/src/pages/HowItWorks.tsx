import React from 'react';
import { ShieldCheck, Search, Calendar, UserCheck, Zap, Layers, Cpu, Award } from 'lucide-react';

export const HowItWorks: React.FC = () => {
  const steps = [
    {
      num: '01',
      title: 'Talent Sourcing & Verification',
      desc: 'Top 1% developers join and undergo a thorough automated credentials check. Their MySQL experience, portfolio scopes, and GitHub repos are parsed directly.',
      icon: <Search className="h-6 w-6 text-brand-primary" />,
      tag: 'Vetting Stage'
    },
    {
      num: '02',
      title: 'Smart Skills Profiling',
      desc: 'Candidates are categorised under verified stack tags. Our system performs deep assessment reviews and ranks profiles based on robust benchmark scores.',
      icon: <ShieldCheck className="h-6 w-6 text-brand-secondary" />,
      tag: 'Benchmarking'
    },
    {
      num: '03',
      title: 'Frictionless Evaluation',
      desc: 'Companies filter candidates in real-time by experience levels and specialized stacks. Detailed profiles are reviewed instantly via streamlined side panels.',
      icon: <Layers className="h-6 w-6 text-indigo-400" />,
      tag: 'Matching Engine'
    },
    {
      num: '04',
      title: 'Certified Contracting',
      desc: 'Once approved, companies trigger secure session setups. Developers start immediately, with full local support and performance caching mechanisms enabled.',
      icon: <UserCheck className="h-6 w-6 text-emerald-400" />,
      tag: 'Onboarding'
    }
  ];

  const highlights = [
    {
      title: 'Zero-Cold Start Teams',
      desc: 'Our cached pre-vetted developer directory reduces your time-to-hire from 35 days to under 48 hours.',
      icon: <Zap className="h-5 w-5 text-amber-400" />
    },
    {
      title: 'Full-Stack Performance',
      desc: 'Verify developers on native architectural paradigms including Web microservices, Node.js triggers, and database clustering.',
      icon: <Cpu className="h-5 w-5 text-rose-400" />
    },
    {
      title: 'Verified Badging System',
      desc: 'Avoid manual interview noise. Code skills, reference reviews, and background declarations are locked on-chain.',
      icon: <Award className="h-5 w-5 text-emerald-400" />
    }
  ];

  return (
    <div className="relative py-16 sm:py-24 overflow-hidden bg-slate-950 text-white">
      {/* Background ambient lighting */}
      <div className="absolute inset-0 bg-grid-ambient opacity-50" />
      <div className="absolute top-20 left-1/2 -translate-x-1/2 h-[350px] w-[600px] rounded-full bg-brand-primary/10 blur-[120px] pointer-events-none" />

      <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        {/* Header */}
        <div className="text-center max-w-3xl mx-auto mb-16 sm:mb-20">
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-primary/10 border border-brand-primary/25 text-xs text-brand-primary font-mono tracking-wider uppercase mb-4">
            <span>Workflow Engine</span>
          </div>
          <h1 className="font-display text-4xl sm:text-5xl font-extrabold tracking-tight mb-6">
            Connecting Elite Developers <br />
            <span className="bg-gradient-to-r from-brand-primary via-indigo-400 to-brand-secondary bg-clip-text text-transparent">
              With Fast Scalability
            </span>
          </h1>
          <p className="text-lg text-slate-400 leading-relaxed">
            From registration to contracting, our platform automates the technical screen process so you only interact with certified world-class professionals.
          </p>
        </div>

        {/* 4-Step grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-20">
          {steps.map((step, idx) => (
            <div 
              key={step.num}
              className="relative group p-6 rounded-2xl glass-panel bg-slate-900/40 hover:bg-slate-900/80 transition-all duration-300"
            >
              {/* Connector lines on tablet+ */}
              {idx < 3 && (
                <div className="hidden lg:block absolute top-12 left-full w-full h-[1px] bg-gradient-to-r from-brand-secondary/30 via-slate-800 to-transparent z-10 -ml-3 pointer-events-none" />
              )}

              <div className="flex items-center justify-between mb-6">
                <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-950 border border-slate-800 group-hover:border-slate-700 transition-all">
                  {step.icon}
                </div>
                <span className="font-mono text-4xl font-black text-slate-800 group-hover:text-brand-secondary/20 transition-all">
                  {step.num}
                </span>
              </div>

              <div className="space-y-2">
                <span className="text-[10px] font-mono tracking-widest text-brand-secondary uppercase block">
                  {step.tag}
                </span>
                <h3 className="font-display text-lg font-bold text-white group-hover:text-brand-secondary transition-all">
                  {step.title}
                </h3>
                <p className="text-sm text-slate-400 leading-relaxed">
                  {step.desc}
                </p>
              </div>
            </div>
          ))}
        </div>

        {/* Highlights Section */}
        <div className="rounded-3xl border border-slate-900/60 bg-gradient-to-b from-slate-900/40 to-slate-950/40 p-8 sm:p-12 glass-panel">
          <div className="text-center max-w-2xl mx-auto mb-10">
            <h2 className="font-display text-2xl font-bold">Engineered for Technical Credibility</h2>
            <p className="text-sm text-slate-400 mt-2">
              Our processes are optimized to cut down administrative noise, ensuring strict engineering-standard quality control.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {highlights.map((h, i) => (
              <div key={i} className="space-y-3">
                <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-950 border border-slate-800">
                  {h.icon}
                </div>
                <h3 className="text-base font-semibold text-white">{h.title}</h3>
                <p className="text-sm text-slate-400 leading-relaxed">{h.desc}</p>
              </div>
            ))}
          </div>
        </div>

      </div>
    </div>
  );
};
