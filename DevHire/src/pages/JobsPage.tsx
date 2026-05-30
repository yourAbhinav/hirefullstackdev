import React, { useState, useEffect } from 'react';
import { Job } from '../types';
import { Search, MapPin, Briefcase, DollarSign, Calendar, Sparkles, Filter, Bookmark, Landmark, ArrowUpRight, HelpCircle, X } from 'lucide-react';

interface JobsPageProps {
  jobs: Job[];
  onApplyForJob: (job: Job) => void;
}

export const JobsPage: React.FC<JobsPageProps> = ({ jobs, onApplyForJob }) => {
  const [search, setSearch] = useState('');
  const [expFilter, setExpFilter] = useState('ALL');
  const [workFilter, setWorkFilter] = useState('ALL');
  const [filteredJobs, setFilteredJobs] = useState<Job[]>(jobs);
  const [activeJob, setActiveJob] = useState<Job | null>(null);
  const [savedJobIds, setSavedJobIds] = useState<string[]>([]);

  // Load bookmarks on init
  useEffect(() => {
    try {
      const bookmarked = JSON.parse(localStorage.getItem('devhire_saved_jobs') || '[]');
      setSavedJobIds(bookmarked);
    } catch (e) {
      console.error(e);
    }
  }, []);

  // Filter computation
  useEffect(() => {
    let result = jobs;

    // Keyword Search query
    if (search.trim()) {
      const q = search.toLowerCase();
      result = result.filter(
        (j) =>
          j.title.toLowerCase().includes(q) ||
          j.companyName.toLowerCase().includes(q) ||
          j.description.toLowerCase().includes(q) ||
          j.location.toLowerCase().includes(q) ||
          j.category.toLowerCase().includes(q) ||
          j.requirements.some((r) => r.toLowerCase().includes(q))
      );
    }

    // Experience filter selection
    if (expFilter !== 'ALL') {
      result = result.filter((j) => j.experienceLevel === expFilter);
    }

    // Work type filter selection
    if (workFilter !== 'ALL') {
      result = result.filter((j) => j.workType === workFilter);
    }

    setFilteredJobs(result);
  }, [search, expFilter, workFilter, jobs]);

  // Handle save toggle persistence
  const toggleSaveJob = (e: React.MouseEvent, jobId: string) => {
    e.stopPropagation();
    let updated = [...savedJobIds];
    if (updated.includes(jobId)) {
      updated = updated.filter((id) => id !== jobId);
    } else {
      updated.push(jobId);
    }
    setSavedJobIds(updated);
    localStorage.setItem('devhire_saved_jobs', JSON.stringify(updated));
  };

  return (
    <div className="relative py-16 sm:py-24 overflow-hidden bg-slate-950 text-white min-h-screen">
      <div className="absolute inset-0 bg-grid-ambient opacity-50" />
      <div className="absolute top-20 left-1/3 h-[400px] w-[600px] rounded-full bg-brand-primary/10 blur-[130px] pointer-events-none" />

      <div className="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        {/* Title structure */}
        <div className="flex flex-col md:flex-row items-start md:items-end justify-between gap-6 mb-12 border-b border-slate-900 pb-8">
          <div>
            <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-secondary/10 border border-brand-secondary/25 text-xs text-brand-secondary font-mono tracking-wider uppercase mb-3">
              <Briefcase className="h-3.5 w-3.5 animate-pulse" />
              <span>Job Pipeline</span>
            </div>
            <h1 className="font-display text-4xl font-extrabold tracking-tight">
              Premium Developer Job Board
            </h1>
            <p className="text-sm text-slate-400 mt-2 max-w-2xl">
              Browse world-class requirements posted by Verified enterprise companies. Transparent salaries, robust architectures, and detailed technical specs.
            </p>
          </div>

          <div className="px-3 py-1.5 rounded-xl border border-slate-900 bg-slate-900/45 text-xs text-slate-400 flex items-center gap-2">
            <span className="h-1.5 w-1.5 rounded-full bg-brand-secondary status-indicator-pulse" />
            <span>{filteredJobs.length} Positions Available</span>
          </div>
        </div>

        {/* Filter Toolbar components */}
        <div className="grid grid-cols-1 md:grid-cols-12 gap-6 bg-slate-900/30 p-6 rounded-3xl border border-slate-900 glass-panel mb-12">
          
          {/* Keyword Field Sorter */}
          <div className="md:col-span-6 relative">
            <label className="text-[10px] font-mono font-bold tracking-widest text-slate-500 uppercase block mb-1.5">Sift keyword roles</label>
            <div className="relative">
              <Search className="absolute left-3.5 top-3 h-4 w-4 text-slate-500" />
              <input
                type="text"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="w-full h-10 pl-10 pr-4 rounded-xl border border-slate-800 bg-slate-950 text-xs text-white placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-brand-primary"
                placeholder="Search matching roles, technologies requirements or salaries..."
              />
            </div>
          </div>

          {/* Exp Filters */}
          <div className="md:col-span-3">
            <label className="text-[10px] font-mono font-bold tracking-widest text-slate-500 uppercase block mb-1.5">Seniority Bounds</label>
            <div className="relative">
              <select
                value={expFilter}
                onChange={(e) => setExpFilter(e.target.value)}
                className="w-full h-10 px-3.5 rounded-xl border border-slate-800 bg-slate-950 text-xs text-slate-300 focus:outline-none focus:ring-1 focus:ring-brand-primary appearance-none cursor-pointer"
              >
                <option value="ALL">All Levels</option>
                <option value="Junior">Junior (1-3 yrs)</option>
                <option value="Mid">Mid-Level (3-5 yrs)</option>
                <option value="Senior">Senior (+5 yrs)</option>
              </select>
              <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                <Filter className="h-3.5 w-3.5" />
              </div>
            </div>
          </div>

          {/* Location Filters */}
          <div className="md:col-span-3">
            <label className="text-[10px] font-mono font-bold tracking-widest text-slate-500 uppercase block mb-1.5">Employment Model</label>
            <div className="relative">
              <select
                value={workFilter}
                onChange={(e) => setWorkFilter(e.target.value)}
                className="w-full h-10 px-3.5 rounded-xl border border-slate-800 bg-slate-950 text-xs text-slate-300 focus:outline-none focus:ring-1 focus:ring-brand-primary appearance-none cursor-pointer"
              >
                <option value="ALL">All Models</option>
                <option value="Remote">100% Remote</option>
                <option value="Hybrid">Hybrid Office</option>
                <option value="On-site">On-site Headquarters</option>
              </select>
              <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-500">
                <Filter className="h-3.5 w-3.5" />
              </div>
            </div>
          </div>

        </div>

        {/* Content catalog grid & slide out panel */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          
          {/* Main List Column */}
          <div className={`col-span-1 space-y-6 ${activeJob ? 'lg:col-span-7' : 'lg:col-span-12'}`}>
            
            {filteredJobs.length === 0 ? (
              <div className="text-center py-20 rounded-3xl border border-slate-900 bg-slate-900/10 glass-panel">
                <Briefcase className="h-10 w-10 text-slate-600 mx-auto mb-4" />
                <h3 className="text-base font-bold text-white">No Matching Openings</h3>
                <p className="text-sm text-slate-400 mt-1 max-w-md mx-auto">
                  No listings found matching your exact filter selections right now. Try clearing filters.
                </p>
                <button
                  onClick={() => { setSearch(''); setExpFilter('ALL'); setWorkFilter('ALL'); }}
                  className="mt-6 px-4 py-2.5 rounded-xl bg-slate-900 text-xs font-semibold hover:bg-slate-850 border border-slate-800 transition-all cursor-pointer text-slate-300"
                >
                  Reset Active Filters
                </button>
              </div>
            ) : (
              filteredJobs.map((job) => {
                const isActive = activeJob?.id === job.id;
                const isSaved = savedJobIds.includes(job.id);

                return (
                  <div
                    key={job.id}
                    onClick={() => setActiveJob(job)}
                    className={`p-6 sm:p-8 rounded-3xl border transition-all duration-300 glass-panel cursor-pointer relative ${
                      isActive 
                        ? 'border-brand-primary bg-slate-900/40 shadow-xl shadow-brand-primary/5' 
                        : 'border-slate-900 bg-slate-900/10 hover:bg-slate-900/30'
                    }`}
                  >
                    
                    {/* Featured flag tag overlay */}
                    {job.featured && (
                      <span className="absolute top-0 left-8 -translate-y-1/2 rounded-full bg-gradient-to-r from-brand-primary to-indigo-500 px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-wider text-white">
                        Featured
                      </span>
                    )}

                    {/* Desktop layout Grid */}
                    <div className="flex flex-col sm:flex-row items-start justify-between gap-4 mb-4">
                      
                      <div className="flex items-start gap-4">
                        {/* Company Icon Badge box */}
                        <div className="h-12 w-12 rounded-xl bg-slate-950 border border-slate-850 flex items-center justify-center text-xl shadow-inner">
                          {job.companyLogo || '💼'}
                        </div>

                        <div>
                          <h3 className="font-display font-extrabold text-lg text-white leading-tight group-hover:text-brand-secondary">{job.title}</h3>
                          <p className="text-xs text-brand-secondary mt-1">{job.companyName}</p>
                        </div>
                      </div>

                      {/* Header right: bookmark & EXP tag */}
                      <div className="flex items-center gap-2">
                        <button
                          onClick={(e) => toggleSaveJob(e, job.id)}
                          className={`p-2 rounded-lg border transition-all cursor-pointer ${
                            isSaved 
                              ? 'bg-brand-primary/10 border-brand-primary text-brand-primary hover:bg-brand-primary/20' 
                              : 'bg-slate-950 border-slate-850 text-slate-500 hover:text-white'
                          }`}
                          title={isSaved ? "Saved to local bookmarks" : "Save this job"}
                        >
                          <Bookmark className={`h-4 w-4 ${isSaved ? 'fill-brand-primary' : ''}`} />
                        </button>

                        <span className="rounded-md bg-slate-950 border border-slate-850 px-2 py-0.5 text-[10px] font-mono tracking-wider text-slate-300 uppercase">
                          {job.experienceLevel} LEVEL
                        </span>
                      </div>

                    </div>

                    <p className="text-xs text-slate-400 leading-relaxed mb-6 line-clamp-2">
                      {job.description}
                    </p>

                    <div className="h-px bg-slate-900/60 mb-6" />

                    {/* Bottom metrics panel */}
                    <div className="flex flex-wrap items-center justify-between gap-4 text-xs text-slate-500 font-medium">
                      <div className="flex items-center gap-4">
                        <span className="flex items-center gap-1.5">
                          <MapPin className="h-3.5 w-3.5 text-slate-600" />
                          {job.location}
                        </span>
                        <span>•</span>
                        <span className="flex items-center gap-1.5">
                          <Briefcase className="h-3.5 w-3.5 text-slate-600" />
                          {job.workType}
                        </span>
                        <span>•</span>
                        <span className="flex items-center gap-1.5">
                          <DollarSign className="h-3.5 w-3.5 text-slate-600" />
                          {job.salary}
                        </span>
                      </div>

                      <span className="text-[10px] text-slate-600 font-mono">
                        Posted: {job.postedAt}
                      </span>
                    </div>

                  </div>
                );
              })
            )}

          </div>

          {/* Secondary Details slide pane (col-span-5) */}
          {activeJob && (
            <div className="lg:col-span-5 rounded-3xl border border-brand-primary/30 bg-slate-900/30 p-6 sm:p-8 glass-panel z-10 sticky top-[90px] duration-300 animate-in slide-in-from-right-4">
              
              <div className="flex items-start justify-between gap-4 mb-6">
                <div>
                  <h3 className="font-display font-extrabold text-xl text-white">{activeJob.title}</h3>
                  <p className="text-sm text-brand-secondary font-medium mt-1">{activeJob.companyName}</p>
                </div>

                <button
                  onClick={() => setActiveJob(null)}
                  className="p-1.5 rounded-lg bg-slate-950 border border-slate-850 hover:bg-slate-900 text-slate-500 hover:text-white transition-all cursor-pointer"
                >
                  <X className="h-4 w-4" />
                </button>
              </div>

              <div className="space-y-6 text-sm">
                
                {/* Meta details list */}
                <div className="grid grid-cols-2 gap-4 bg-slate-950/80 p-4 rounded-2xl border border-slate-900 text-xs">
                  <div>
                    <span className="text-slate-500 block mb-0.5 uppercase tracking-wider text-[9px] font-mono">Salary Package</span>
                    <span className="font-bold text-white text-sm">{activeJob.salary}</span>
                  </div>
                  <div>
                    <span className="text-slate-500 block mb-0.5 uppercase tracking-wider text-[9px] font-mono">Location Mode</span>
                    <span className="font-bold text-white text-sm">{activeJob.location} • {activeJob.workType}</span>
                  </div>
                </div>

                {/* Description info */}
                <div>
                  <h4 className="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-2 font-mono">Overview</h4>
                  <p className="text-xs text-slate-300 leading-relaxed bg-slate-900/20 p-2.5 rounded-xl border border-slate-900">
                    {activeJob.description}
                  </p>
                </div>

                {/* Requirements check panel */}
                <div>
                  <h4 className="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-2.5 font-mono">Benchmark Requirements</h4>
                  <ul className="space-y-2.5 text-xs">
                    {activeJob.requirements.map((req, idx) => (
                      <li key={idx} className="flex items-start gap-2">
                        <span className="h-1.5 w-1.5 rounded-full bg-brand-secondary mt-1.5 flex-shrink-0" />
                        <span className="text-slate-300 leading-normal">{req}</span>
                      </li>
                    ))}
                  </ul>
                </div>

                <div className="h-px bg-slate-900/60 pt-2" />

                {/* Apply button link */}
                <button
                  onClick={() => onApplyForJob(activeJob)}
                  className="w-full py-3.5 rounded-xl bg-gradient-to-r from-brand-primary to-indigo-500 text-center font-bold text-sm text-white shadow-lg shadow-brand-primary/20 hover:shadow-brand-primary/40 transition-all cursor-pointer flex items-center justify-center gap-2"
                >
                  <span>Apply Now for this Position</span>
                  <ArrowUpRight className="h-4 w-4" />
                </button>

              </div>

            </div>
          )}

        </div>

      </div>
    </div>
  );
};
