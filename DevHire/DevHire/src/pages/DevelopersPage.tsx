import React, { useState, useEffect } from 'react';
import { Developer } from '../types';
import { Search, MapPin, Calendar, CheckSquare, Sparkles, Filter, Code, Award, ExternalLink } from 'lucide-react';

interface DevelopersPageProps {
  developers: Developer[];
}

export const DevelopersPage: React.FC<DevelopersPageProps> = ({ developers }) => {
  const [search, setSearch] = useState('');
  const [techFilter, setTechFilter] = useState('ALL');
  const [experienceFilter, setExperienceFilter] = useState('ALL');
  const [filteredDevs, setFilteredDevs] = useState<Developer[]>(developers);

  // List of all tech specializations
  const allTechs = ['ALL', 'TypeScript', 'React', 'Node.js', 'Express', 'MySQL', 'Firebase', 'PHP', 'MongoDB', 'PostgreSQL', 'Docker', 'AWS'];

  useEffect(() => {
    let result = developers;

    // Keyword Search
    if (search.trim()) {
      const q = search.toLowerCase();
      result = result.filter(
        (d) =>
          d.fullName.toLowerCase().includes(q) ||
          d.title.toLowerCase().includes(q) ||
          d.bio.toLowerCase().includes(q) ||
          d.location.toLowerCase().includes(q) ||
          d.techStack.some((t) => t.toLowerCase().includes(q))
      );
    }

    // Technology Stack Filter
    if (techFilter !== 'ALL') {
      result = result.filter((d) => d.techStack.includes(techFilter));
    }

    // Experience Filter
    if (experienceFilter !== 'ALL') {
      result = result.filter((d) => {
        const years = parseInt(d.experience);
        if (experienceFilter === 'Junior') return years <= 3;
        if (experienceFilter === 'Mid') return years > 3 && years <= 5;
        if (experienceFilter === 'Senior') return years > 5;
        return true;
      });
    }

    setFilteredDevs(result);
  }, [search, techFilter, experienceFilter, developers]);

  return (
    <div className="relative py-16 sm:py-24 overflow-hidden bg-slate-950 text-white">
      <div className="absolute inset-0 bg-grid-ambient opacity-50" />
      <div className="absolute top-20 right-1/4 h-[350px] w-[600px] rounded-full bg-brand-secondary/10 blur-[130px] pointer-events-none" />

      <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        {/* Header section with styling matching sitemap guides */}
        <div className="flex flex-col md:flex-row items-start md:items-end justify-between gap-6 mb-12 border-b border-slate-900 pb-8">
          <div>
            <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-xs text-indigo-400 font-mono tracking-wider uppercase mb-3">
              <Code className="h-3.5 w-3.5" />
              <span>Developer Directory</span>
            </div>
            <h1 className="font-display text-4xl font-extrabold tracking-tight">
              Elite Engineering Directory
            </h1>
            <p className="text-sm text-slate-400 mt-2 max-w-2xl">
              Meet pre-vetted full stack experts. Review GitHub contributions, tested benchmark stack matrices, and verified cloud engineering histories.
            </p>
          </div>

          <div className="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900/60 border border-slate-800 text-xs text-slate-400">
            <span className="h-2 w-2 rounded-full bg-emerald-500 status-indicator-pulse" />
            <span>{filteredDevs.length} Certified Experts Available</span>
          </div>
        </div>

        {/* Filter Controls Box */}
        <div className="grid grid-cols-1 md:grid-cols-12 gap-6 bg-slate-900/30 p-6 rounded-3xl border border-slate-900 glass-panel mb-10">
          
          {/* Keyword Search */}
          <div className="md:col-span-6 relative">
            <label className="text-[10px] font-mono font-bold tracking-widest text-slate-500 uppercase block mb-1.5">Search Keywords</label>
            <div className="relative">
              <Search className="absolute left-3.5 top-3 h-4 w-4 text-slate-500" />
              <input
                type="text"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="w-full h-10 pl-10 pr-4 rounded-xl border border-slate-800 bg-slate-950 text-xs text-white placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-brand-primary"
                placeholder="Search name, specializations, locations or bios..."
              />
            </div>
          </div>

          {/* Specialization Filter */}
          <div className="md:col-span-3">
            <label className="text-[10px] font-mono font-bold tracking-widest text-slate-500 uppercase block mb-1.5">Primary Tech Stack</label>
            <div className="relative">
              <select
                value={techFilter}
                onChange={(e) => setTechFilter(e.target.value)}
                className="w-full h-10 px-3.5 rounded-xl border border-slate-800 bg-slate-950 text-xs text-slate-300 focus:outline-none focus:ring-1 focus:ring-brand-primary cursor-pointer appearance-none"
              >
                {allTechs.map((t) => (
                  <option key={t} value={t} className="bg-slate-950 text-slate-300">
                    {t === 'ALL' ? 'All Technologies' : t}
                  </option>
                ))}
              </select>
              <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                <Filter className="h-3.5 w-3.5" />
              </div>
            </div>
          </div>

          {/* Experience Filter */}
          <div className="md:col-span-3">
            <label className="text-[10px] font-mono font-bold tracking-widest text-slate-500 uppercase block mb-1.5">Seniority Bracket</label>
            <div className="relative">
              <select
                value={experienceFilter}
                onChange={(e) => setExperienceFilter(e.target.value)}
                className="w-full h-10 px-3.5 rounded-xl border border-slate-800 bg-slate-950 text-xs text-slate-300 focus:outline-none focus:ring-1 focus:ring-brand-primary cursor-pointer appearance-none"
              >
                <option value="ALL">All Seniority</option>
                <option value="Junior">Junior (≤ 3 Years)</option>
                <option value="Mid">Mid-Level (3-5 Years)</option>
                <option value="Senior">Senior (&gt; 5 Years)</option>
              </select>
              <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                <Filter className="h-3.5 w-3.5" />
              </div>
            </div>
          </div>

        </div>

        {/* Developers Catalog Grid */}
        {filteredDevs.length === 0 ? (
          <div className="text-center py-20 rounded-3xl border border-slate-900 bg-slate-900/10 glass-panel">
            <Award className="h-10 w-10 text-slate-600 mx-auto mb-4" />
            <h3 className="text-base font-bold text-white">No Matching Engineers</h3>
            <p className="text-xs text-slate-400 mt-1 max-w-md mx-auto">
              We couldn&apos;t find verified candidates matching your selected stack. Try clearing filter selectors or broadening keywords.
            </p>
            <button
              onClick={() => { setSearch(''); setTechFilter('ALL'); setExperienceFilter('ALL'); }}
              className="mt-6 px-4 py-2.5 rounded-xl bg-slate-900 text-xs font-semibold hover:bg-slate-850 border border-slate-800 transition-all cursor-pointer text-slate-300"
            >
              Reset Filters
            </button>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            {filteredDevs.map((dev) => (
              <div 
                key={dev.id}
                className="relative p-6 sm:p-8 rounded-3xl border border-slate-900/60 bg-slate-900/10 hover:bg-slate-900/30 transition-all duration-300 flex flex-col justify-between glass-panel glass-panel-hover"
              >
                <div>
                  
                  {/* Top line with Avatar and verification badge */}
                  <div className="flex items-start justify-between gap-4 mb-6">
                    <div className="flex items-center gap-4">
                      {dev.profileImage ? (
                        <img 
                          src={dev.profileImage} 
                          alt={dev.fullName} 
                          className="h-14 w-14 rounded-2xl object-cover border border-slate-800 bg-slate-950"
                        />
                      ) : (
                        <div className="h-14 w-14 rounded-2xl bg-brand-primary/10 flex items-center justify-center text-brand-primary font-display font-bold text-lg border border-brand-primary/20">
                          {dev.fullName.charAt(0)}
                        </div>
                      )}

                      <div>
                        <div className="flex items-center gap-1.5">
                          <h3 className="font-display font-extrabold text-lg text-white leading-tight">{dev.fullName}</h3>
                          {dev.verified && (
                            <span className="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[10px]" title="Vetted Credentials License">
                              ✓
                            </span>
                          )}
                        </div>
                        <p className="text-xs text-slate-400 mt-0.5">{dev.title}</p>
                      </div>
                    </div>

                    <div className="flex flex-col items-end gap-1.5">
                      <span className="rounded-md bg-slate-950 border border-slate-800 px-2 py-0.5 text-[10px] font-mono tracking-wider text-slate-300 uppercase">
                        {dev.experience} EXP
                      </span>
                      <span className="text-[10px] text-slate-500 font-medium flex items-center gap-1">
                        <MapPin className="h-3 w-3 text-brand-secondary" />
                        {dev.location}
                      </span>
                    </div>
                  </div>

                  <p className="text-xs text-slate-400 leading-relaxed mb-6">
                    {dev.bio}
                  </p>

                  <div className="h-px bg-slate-900/60 mb-6" />

                  {/* Tech stack pills */}
                  <div className="space-y-1.5">
                    <span className="text-[9px] font-mono font-bold tracking-widest text-slate-500 uppercase block">Benchmark Specialities</span>
                    <div className="flex flex-wrap gap-1.5">
                      {dev.techStack.map((tech) => (
                        <span 
                          key={tech} 
                          className={`rounded-full px-2.5 py-0.5 text-[10px] font-medium border transition-all ${
                            techFilter === tech
                              ? 'bg-brand-primary/20 text-brand-primary border-brand-primary/40'
                              : 'bg-slate-950 text-slate-300 border-slate-850'
                          }`}
                        >
                          {tech}
                        </span>
                      ))}
                    </div>
                  </div>

                </div>

                {/* Resume actions / External view options */}
                {dev.portfolioUrl && (
                  <div className="mt-8 pt-4 border-t border-slate-900/60 flex items-center justify-between">
                    <a 
                      href={dev.portfolioUrl}
                      target="_blank"
                      rel="noreferrer"
                      className="text-xs text-brand-secondary hover:text-white transition-all flex items-center gap-1 cursor-pointer font-medium"
                    >
                      <span>Review Portfolio Hub</span>
                      <ExternalLink className="h-3.5 w-3.5" />
                    </a>
                    
                    <span className="text-[10px] text-slate-500 font-mono tracking-wider uppercase">
                      MySQL & Stack Certified
                    </span>
                  </div>
                )}

              </div>
            ))}
          </div>
        )}

      </div>
    </div>
  );
};
