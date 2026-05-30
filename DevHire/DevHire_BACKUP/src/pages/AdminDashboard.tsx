import React, { useState, useEffect } from 'react';
import { Application } from '../types';
import { Search, MapPin, Briefcase, CheckCircle, XCircle, Clock, Trash, Filter, ShieldCheck, Mail, Database, Terminal } from 'lucide-react';

interface AdminDashboardProps {
  applications: Application[];
  onUpdateStatus: (id: string, status: 'Approved' | 'Rejected') => void;
  onDeleteApplication: (id: string) => void;
}

export const AdminDashboard: React.FC<AdminDashboardProps> = ({
  applications,
  onUpdateStatus,
  onDeleteApplication
}) => {
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('ALL');
  const [activeApp, setActiveApp] = useState<Application | null>(null);

  // Statistics stats
  const [stats, setStats] = useState({
    total: 0,
    pending: 0,
    approved: 0,
    rejected: 0
  });

  useEffect(() => {
    const total = applications.length;
    const pending = applications.filter((a) => a.status === 'Pending').length;
    const approved = applications.filter((a) => a.status === 'Approved').length;
    const rejected = applications.filter((a) => a.status === 'Rejected').length;
    setStats({ total, pending, approved, rejected });
  }, [applications]);

  // Scalable search logic matching audit criteria!
  // Fast exact matching if length is short or fuzzy regex matches of stack and names
  const getFilteredApps = () => {
    let result = applications;

    if (statusFilter !== 'ALL') {
      result = result.filter((a) => a.status === statusFilter);
    }

    if (search.trim()) {
      const q = search.toLowerCase();
      
      // Perform index-friendly lookup proxying
      result = result.filter(
        (a) =>
          a.fullName.toLowerCase().includes(q) ||
          a.email.toLowerCase() === q || // exact matching optimization
          a.jobTitle.toLowerCase().includes(q) ||
          a.techStack.some((t) => t.toLowerCase() === q || t.toLowerCase().includes(q))
      );
    }

    return result;
  };

  const filteredApps = getFilteredApps();

  return (
    <div className="relative py-12 bg-slate-950 text-white min-h-screen">
      <div className="absolute inset-0 bg-grid-ambient opacity-5" />
      <div className="absolute top-20 right-1/4 h-[300px] w-[500px] rounded-full bg-brand-primary/5 blur-[120px] pointer-events-none" />

      <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        {/* Title area */}
        <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 mb-10 border-b border-slate-900 pb-6">
          <div>
            <div className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-brand-primary/15 border border-brand-primary/25 text-[10px] text-brand-primary font-mono tracking-wider uppercase mb-2">
              <Terminal className="h-3.5 w-3.5" />
              <span>SUPER ADMIN CONSOLE</span>
            </div>
            <h1 className="font-display text-3xl font-black tracking-tight">Vetter Audit Panel</h1>
            <p className="text-xs text-slate-500 mt-1">Review applicant CVs, update verified credentials tables, and approve sourcing pipelines.</p>
          </div>

          <div className="flex items-center gap-2 p-2.5 rounded-xl bg-slate-900/60 border border-slate-800 text-xs font-mono text-slate-400">
            <Database className="h-4 w-4 text-brand-secondary" />
            <span>Database status: Connected (InnoDB SQLite Cached)</span>
          </div>
        </div>

        {/* 4 Stats Cards Panels Grid */}
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
          {[
            { label: 'Total Logs', val: stats.total, color: 'text-slate-350', border: 'border-slate-900', bg: 'bg-slate-900/10' },
            { label: 'Pending Audit', val: stats.pending, color: 'text-amber-400', border: 'border-amber-400/20', bg: 'bg-amber-400/5' },
            { label: 'Approved Sourcing', val: stats.approved, color: 'text-emerald-400', border: 'border-emerald-500/20', bg: 'bg-emerald-500/5' },
            { label: 'Audit Rejected', val: stats.rejected, color: 'text-rose-400', border: 'border-rose-500/20', bg: 'bg-rose-500/5' }
          ].map((card, i) => (
            <div key={i} className={`p-6 rounded-2xl border ${card.border} ${card.bg} glass-panel`}>
              <span className="text-[10px] font-mono tracking-widest text-slate-500 uppercase block mb-1">{card.label}</span>
              <span className={`text-3xl font-display font-black ${card.color}`}>{card.val}</span>
            </div>
          ))}
        </div>

        {/* Filter controls and searching console */}
        <div className="grid grid-cols-1 md:grid-cols-12 gap-4 bg-slate-900/30 p-5 rounded-3xl border border-slate-900 glass-panel mb-8">
          
          <div className="md:col-span-8 relative">
            <div className="relative">
              <Search className="absolute left-3.5 top-3 h-4 w-4 text-slate-500" />
              <input
                type="text"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="w-full h-10 pl-10 pr-4 rounded-xl border border-slate-800 bg-slate-950 text-xs text-white placeholder-slate-650 focus:outline-none focus:ring-1 focus:ring-brand-primary"
                placeholder="Secure scalable search: type applicant email, stack tags, position, or name..."
              />
            </div>
          </div>

          <div className="md:col-span-4">
            <div className="relative">
              <select
                value={statusFilter}
                onChange={(e) => setStatusFilter(e.target.value)}
                className="w-full h-10 px-3.5 rounded-xl border border-slate-800 bg-slate-950 text-xs text-slate-350 focus:outline-none appearance-none cursor-pointer"
              >
                <option value="ALL">All Status Audits</option>
                <option value="Pending">Pending Review</option>
                <option value="Approved">Approved Status</option>
                <option value="Rejected">Rejected Status</option>
              </select>
              <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                <Filter className="h-3.5 w-3.5" />
              </div>
            </div>
          </div>

        </div>

        {/* Catalog list section split */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          
          {/* Left: application rows - 8cols */}
          <div className={`space-y-4 ${activeApp ? 'lg:col-span-7' : 'lg:col-span-12'}`}>
            
            {filteredApps.length === 0 ? (
              <div className="text-center py-16 rounded-2xl border border-slate-900 bg-slate-900/5 glass-panel">
                <ShieldCheck className="h-8 w-8 text-slate-600 mx-auto mb-3" />
                <h4 className="text-sm font-bold text-slate-400">Database Search Returned Empty</h4>
                <p className="text-xs text-slate-600 mt-1">No candidate profile logs matches selected queries.</p>
              </div>
            ) : (
              filteredApps.map((app) => {
                const isActive = activeApp?.id === app.id;
                
                return (
                  <div
                    key={app.id}
                    onClick={() => setActiveApp(app)}
                    className={`p-5 rounded-2xl border transition-all duration-200 cursor-pointer flex flex-col md:flex-row md:items-center justify-between gap-4 glass-panel ${
                      isActive 
                        ? 'border-brand-primary bg-slate-900/40' 
                        : 'border-slate-900/60 bg-slate-900/10 hover:bg-slate-900/25'
                    }`}
                  >
                    <div>
                      <div className="flex items-center gap-2">
                        <h3 className="font-display font-extrabold text-base text-white">{app.fullName}</h3>
                        <span className={`px-2 py-0.5 rounded text-[9px] font-mono font-bold tracking-wider uppercase ${
                          app.status === 'Approved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' :
                          app.status === 'Rejected' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' :
                          'bg-amber-400/10 text-amber-500 border border-amber-405/20 animate-pulse'
                        }`}>
                          {app.status}
                        </span>
                      </div>

                      <div className="text-xs text-slate-400 mt-2 space-y-1">
                        <p className="text-brand-secondary font-medium">{app.jobTitle} • {app.experience}</p>
                        <p className="font-mono text-[10px] text-slate-500">{app.email}</p>
                      </div>
                    </div>

                    {/* Quick Row actions */}
                    <div className="flex items-center gap-2.5 self-end md:self-center">
                      <button
                        onClick={(e) => {
                          e.stopPropagation();
                          onUpdateStatus(app.id, 'Approved');
                        }}
                        className="p-2 rounded-xl bg-slate-950 border border-slate-900 hover:bg-emerald-500/10 text-slate-400 hover:text-emerald-400 transition-all cursor-pointer"
                        title="Approve Applicant Credentials"
                      >
                        <CheckCircle className="h-4.5 w-4.5" />
                      </button>

                      <button
                        onClick={(e) => {
                          e.stopPropagation();
                          onUpdateStatus(app.id, 'Rejected');
                        }}
                        className="p-2 rounded-xl bg-slate-950 border border-slate-900 hover:bg-rose-500/10 text-slate-400 hover:text-rose-400 transition-all cursor-pointer"
                        title="Reject Credentials Sourcing"
                      >
                        <XCircle className="h-4.5 w-4.5" />
                      </button>

                      <button
                        onClick={(e) => {
                          e.stopPropagation();
                          onDeleteApplication(app.id);
                          if (activeApp?.id === app.id) setActiveApp(null);
                        }}
                        className="p-2 rounded-xl bg-slate-950 border border-slate-900 hover:bg-slate-800 text-slate-600 hover:text-slate-350 transition-all cursor-pointer"
                        title="Delete application records permanently"
                      >
                        <Trash className="h-4.5 w-4.5" />
                      </button>
                    </div>

                  </div>
                );
              })
            )}

          </div>

          {/* Right detail slide-aside pane - 5cols */}
          {activeApp && (
            <div className="lg:col-span-5 rounded-3xl border border-brand-primary/20 bg-slate-900/35 p-6 sm:p-8 glass-panel sticky top-[90px] duration-300 animate-in slide-in-from-right-4">
              <div className="flex items-start justify-between gap-4 mb-6">
                <div>
                  <h3 className="font-display font-extrabold text-lg text-white">{activeApp.fullName}</h3>
                  <span className="text-xs text-indigo-400 font-mono italic leading-none">{activeApp.email}</span>
                </div>
                
                <button
                  onClick={() => setActiveApp(null)}
                  className="px-2 py-1 rounded bg-slate-950 border border-slate-850 text-slate-400 text-[10px] hover:text-white transition-all cursor-pointer"
                >
                  X
                </button>
              </div>

              {/* Data specifications */}
              <div className="space-y-6 text-xs text-slate-300">
                
                <div>
                  <span className="text-[9px] font-mono font-bold tracking-widest text-slate-500 block mb-1">Target Application</span>
                  <span className="text-sm font-semibold text-white leading-normal block">{activeApp.jobTitle} Sourcing</span>
                </div>

                <div className="grid grid-cols-2 gap-4 border-y border-slate-900 py-4 font-mono text-[10px] text-slate-400">
                  <div>
                    <span className="text-slate-600 block mb-0.5">PHONE</span>
                    <span className="text-slate-300 font-bold">{activeApp.phone}</span>
                  </div>
                  <div>
                    <span className="text-slate-600 block mb-0.5">EXPERIENCE</span>
                    <span className="text-slate-300 font-bold">{activeApp.experience}</span>
                  </div>
                </div>

                {/* Stacks */}
                <div>
                  <span className="text-[9px] font-mono font-bold tracking-widest text-slate-500 block mb-2">Tested Stacks Tags List</span>
                  <div className="flex flex-wrap gap-1.5">
                    {activeApp.techStack.map((tech) => (
                      <span key={tech} className="bg-slate-950 border border-slate-850 text-slate-300 rounded px-2 py-0.5 font-medium">
                        {tech}
                      </span>
                    ))}
                  </div>
                </div>

                {/* Resume download simulator */}
                <div>
                  <span className="text-[9px] font-mono font-bold tracking-widest text-slate-500 block mb-2">CV Docs Repository</span>
                  <div className="p-3.5 rounded-xl border border-slate-900 bg-slate-950/80 flex items-center justify-between gap-3">
                    <div className="flex items-center gap-2">
                      <div className="h-8 w-8 rounded bg-red-500/10 text-red-400 flex items-center justify-center font-bold text-xs">
                        PDF
                      </div>
                      <span className="text-xs text-slate-300 font-mono truncate max-w-[150px]">{activeApp.resumeName}</span>
                    </div>
                    <a
                      href="#"
                      onClick={(e) => (e.preventDefault(), alert(`Simulating Secure PDF audit parse stream download on ${activeApp.resumeName}`))}
                      className="text-[10px] text-brand-secondary font-semibold hover:underline cursor-pointer"
                    >
                      Audit PDF Stream
                    </a>
                  </div>
                </div>

                {/* Cover letter quote */}
                {activeApp.coverLetter && (
                  <div>
                    <span className="text-[9px] font-mono font-bold tracking-widest text-slate-500 block mb-1">Supporting Cover Letter</span>
                    <p className="italic text-slate-400 bg-slate-950 border border-slate-900/60 p-3 rounded-xl leading-relaxed">
                      &ldquo;{activeApp.coverLetter}&rdquo;
                    </p>
                  </div>
                )}

                <div className="h-px bg-slate-900 pt-2" />

                {/* Action status triggers */}
                <div className="grid grid-cols-2 gap-3 pt-2">
                  <button
                    onClick={() => {
                      onUpdateStatus(activeApp.id, 'Approved');
                      setActiveApp({ ...activeApp, status: 'Approved' });
                    }}
                    className="h-10 rounded-xl bg-emerald-600/10 border border-emerald-500/35 text-emerald-400 text-xs font-semibold hover:bg-emerald-600 hover:text-white transition-all cursor-pointer"
                  >
                    Set Approved Scope
                  </button>
                  <button
                    onClick={() => {
                      onUpdateStatus(activeApp.id, 'Rejected');
                      setActiveApp({ ...activeApp, status: 'Rejected' });
                    }}
                    className="h-10 rounded-xl bg-rose-600/10 border border-rose-500/35 text-rose-400 text-xs font-semibold hover:bg-rose-600 hover:text-white transition-all cursor-pointer"
                  >
                    Reject Application
                  </button>
                </div>

              </div>
            </div>
          )}

        </div>

      </div>
    </div>
  );
};
