import React from 'react';
import { SITE_CONFIG } from '../config';
import { ArrowUpRight, ShieldCheck, Sparkles, Star, Users, Briefcase, Award, ArrowRight, ShieldCheckIcon } from 'lucide-react';
import { Testimonial } from '../types';

interface HeroSectionProps {
  onNavigate: (page: string) => void;
  testimonials: Testimonial[];
}

export const HeroSection: React.FC<HeroSectionProps> = ({ onNavigate, testimonials }) => {
  const stats = [
    { label: 'Verified Engineers', val: '2,400+', icon: <Users className="h-5 w-5 text-brand-primary" /> },
    { label: 'Active Openings', val: '650+', icon: <Briefcase className="h-5 w-5 text-brand-secondary" /> },
    { label: 'Average SLA Match', val: '1.2h', icon: <Sparkles className="h-5 w-5 text-indigo-400" /> },
    { label: 'Sponsor Enterprises', val: '180+', icon: <Award className="h-5 w-5 text-emerald-400" /> }
  ];

  const technologies = [
    { name: 'TypeScript', color: 'border-blue-500/20 text-blue-400' },
    { name: 'React', color: 'border-cyan-500/20 text-cyan-400' },
    { name: 'Node.js', color: 'border-emerald-500/20 text-emerald-400' },
    { name: 'Express', color: 'border-slate-500/20 text-slate-350' },
    { name: 'MySQL', color: 'border-blue-600/20 text-blue-550' },
    { name: 'PHP', color: 'border-purple-500/20 text-purple-400' },
    { name: 'MongoDB', color: 'border-emerald-600/20 text-emerald-500' },
    { name: 'PostgreSQL', color: 'border-indigo-500/20 text-indigo-400' },
    { name: 'AWS Cloud', color: 'border-amber-500/20 text-amber-500' }
  ];

  return (
    <div className="relative overflow-hidden bg-slate-950 text-white py-16 sm:py-24">
      {/* Background patterns */}
      <div className="absolute inset-0 bg-grid-ambient opacity-50" />
      <div className="absolute top-0 left-1/2 -translate-x-1/2 h-[500px] w-[800px] rounded-full bg-brand-primary/10 blur-[130px] pointer-events-none" />

      <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        {/* HERO HEADER AREA */}
        <div className="text-center max-w-5xl mx-auto space-y-6 sm:space-y-8 mb-20">
          
          <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-primary/10 border border-brand-primary/30 text-xs text-brand-primary font-mono tracking-wider uppercase">
            <ShieldCheck className="h-4 w-4 animate-pulse" />
            <span>CERTIFIED TALENT POOL V2.0</span>
          </div>

          <h1 className="font-display text-4xl sm:text-6xl font-black tracking-tight leading-[1.1] mb-2">
            The Sourcing Platform <br />
            For{' '}
            <span className="bg-gradient-to-r from-brand-primary via-indigo-400 to-brand-secondary bg-clip-text text-transparent">
              Verified Core Developers
            </span>
          </h1>

          <p className="text-sm sm:text-base text-slate-400 max-w-2xl mx-auto leading-relaxed">
            Eliminate candidate tracking failures. Secure contract boards, verified skills badges, and direct developer sourcing logs managed seamlessly under flat, non-percentage plans.
          </p>

          <div className="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
            <button
              onClick={() => onNavigate('jobs')}
              className="w-full sm:w-auto h-12 px-6 rounded-xl bg-gradient-to-r from-brand-primary to-indigo-500 text-xs font-bold hover:opacity-95 shadow-xl shadow-brand-primary/20 hover:shadow-brand-primary/45 transition-all cursor-pointer flex items-center justify-center gap-2"
            >
              <span>Sift Active Job Postings</span>
              <ArrowUpRight className="h-4 w-4" />
            </button>
            <button
              onClick={() => onNavigate('developers')}
              className="w-full sm:w-auto h-12 px-6 rounded-xl bg-slate-900 hover:bg-slate-850 hover:text-white border border-slate-800 text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-2 text-slate-300"
            >
              <span>Explore Developer Roster</span>
              <ArrowRight className="h-4 w-4" />
            </button>
          </div>

        </div>

        {/* 4 STATS COUNTER PANELS */}
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-6 bg-slate-900/10 p-6 rounded-3xl border border-slate-900/60 glass-panel mb-24">
          {stats.map((stat, i) => (
            <div key={i} className="flex items-center gap-4 p-4 rounded-2xl bg-slate-1000/40 border border-slate-850 shadow-inner">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-950 border border-slate-800">
                {stat.icon}
              </div>
              <div>
                <span className="text-[10px] font-mono tracking-widest text-slate-500 uppercase block mb-0.5">{stat.label}</span>
                <span className="text-xl font-display font-bold text-white block">{stat.val}</span>
              </div>
            </div>
          ))}
        </div>

        {/* PROCESS FLOW TIMELINE SECTION */}
        <div className="mb-24">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <h2 className="font-display text-3xl font-bold tracking-tight text-white">Our 4-Step Sourcing Workflow</h2>
            <p className="text-xs text-slate-400 mt-2">Connecting verified top 1% engineers with zero administrative friction.</p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
            {[
              { title: 'Sourcing Check', desc: 'Candidates link active profiles and pass background validations.' },
              { title: 'Skills Profiling', desc: 'Our team assesses developer stack codes against benchmark specifications.' },
              { title: 'Open Exploration', desc: 'Sponsors scan directory listings with custom experience filter brackets.' },
              { title: 'Immediate Contracts', desc: 'Approve applicants, lock secure payments, and trigger onboarding pipelines.' }
            ].map((step, idx) => (
              <div key={idx} className="p-6 rounded-2xl bg-slate-900/20 border border-slate-900/60 glass-panel text-left space-y-3 relative group">
                <span className="text-5xl font-mono text-slate-900 font-extrabold block group-hover:text-brand-primary/20 transition-all">0{idx + 1}</span>
                <h4 className="font-display font-semibold text-white text-base group-hover:text-brand-secondary transition-all">{step.title}</h4>
                <p className="text-xs text-slate-400 leading-relaxed">{step.desc}</p>
              </div>
            ))}
          </div>
        </div>

        {/* VERIFIED TECH BADGES GRID SHOWCASE */}
        <div className="mb-24 text-center">
          <h3 className="text-xs font-mono font-bold tracking-widest text-slate-500 uppercase mb-8">
            REVIEWS COMPLETED ACROSS CORE PARADIGMS
          </h3>
          <div className="flex flex-wrap justify-center gap-3 max-w-4xl mx-auto">
            {technologies.map((tech) => (
              <span
                key={tech.name}
                className={`rounded-full border bg-slate-900/10 px-4 py-2 text-xs font-medium ${tech.color} glass-panel hover:scale-105 transition-all`}
              >
                {tech.name}
              </span>
            ))}
          </div>
        </div>

        {/* RECENT SPONSOR TESTIMONIAL CARDS */}
        <div className="border-t border-slate-900 pt-16">
          <div className="text-center max-w-xl mx-auto mb-12">
            <h2 className="font-display text-2xl font-bold">What Sourcing Leaders Say</h2>
            <p className="text-xs text-slate-500 mt-1">Direct quotes from validated vice presidents and tech leads.</p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            {testimonials.map((t) => (
              <div
                key={t.id}
                className="p-6 rounded-3xl border border-slate-900 bg-slate-900/10 glass-panel flex flex-col justify-between"
              >
                <div>
                  <div className="flex items-center gap-1 text-amber-400 mb-4">
                    {[...Array(t.rating)].map((_, i) => (
                      <Star key={i} className="h-3.5 w-3.5 fill-amber-400" />
                    ))}
                  </div>

                  <p className="text-xs text-slate-400 leading-relaxed italic">
                    &ldquo;{t.text}&rdquo;
                  </p>
                </div>

                <div className="flex items-center gap-3.5 border-t border-slate-900/60 pt-4 mt-6">
                  {t.image ? (
                    <img src={t.image} alt={t.name} className="h-9 w-9 rounded-xl object-cover" />
                  ) : (
                    <div className="h-9 w-9 rounded-xl bg-brand-primary/10 flex items-center justify-center text-brand-primary font-bold text-xs">
                      {t.name.charAt(0)}
                    </div>
                  )}
                  <div>
                    <span className="font-display font-extrabold text-xs text-white leading-tight block">{t.name}</span>
                    <span className="text-[10px] text-slate-500 block">{t.role} @ <b className="text-slate-400">{t.company || 'Enterprise'}</b></span>
                  </div>
                </div>

              </div>
            ))}
          </div>
        </div>

      </div>
    </div>
  );
};
