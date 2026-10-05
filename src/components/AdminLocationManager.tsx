import React, { useState } from 'react';
import {
  MapPin,
  Building,
  Plus,
  Trash2,
  Edit2,
  Check,
  X,
  Search,
  ChevronRight,
  Power,
  Layers,
} from 'lucide-react';
import { District, Tehsil, AreaLocation } from '../types';

interface AdminLocationManagerProps {
  districts: District[];
  tehsils: Tehsil[];
  areas: AreaLocation[];
  onAddDistrict: (name: string) => void;
  onUpdateDistrict: (id: number, updates: Partial<District>) => void;
  onDeleteDistrict: (id: number) => void;
  onAddTehsil: (name: string, districtId: number) => void;
  onUpdateTehsil: (id: number, updates: Partial<Tehsil>) => void;
  onDeleteTehsil: (id: number) => void;
  onAddArea: (name: string, tehsilId: number) => void;
  onUpdateArea: (id: number, updates: Partial<AreaLocation>) => void;
  onDeleteArea: (id: number) => void;
}

export const AdminLocationManager: React.FC<AdminLocationManagerProps> = ({
  districts,
  tehsils,
  areas,
  onAddDistrict,
  onUpdateDistrict,
  onDeleteDistrict,
  onAddTehsil,
  onUpdateTehsil,
  onDeleteTehsil,
  onAddArea,
  onUpdateArea,
  onDeleteArea,
}) => {
  const [activeSubTab, setActiveSubTab] = useState<'hierarchy' | 'districts' | 'tehsils' | 'areas'>('hierarchy');
  const [selectedDistrictId, setSelectedDistrictId] = useState<number>(1); // Default Sargodha
  const [selectedTehsilId, setSelectedTehsilId] = useState<number>(7); // Default Sillanwali
  const [searchQuery, setSearchQuery] = useState('');

  // Modals / Inline Add & Edit states
  const [editingItem, setEditingItem] = useState<{ type: 'district' | 'tehsil' | 'area'; id: number; name: string; parentId?: number } | null>(null);
  const [newDistrictName, setNewDistrictName] = useState('');
  const [newTehsilName, setNewTehsilName] = useState('');
  const [newTehsilDistrictId, setNewTehsilDistrictId] = useState<number>(1);
  const [newAreaName, setNewAreaName] = useState('');
  const [newAreaTehsilId, setNewAreaTehsilId] = useState<number>(7);

  // Stats
  const activeDistricts = districts.filter((d) => d.isActive).length;
  const activeTehsils = tehsils.filter((t) => t.isActive).length;
  const activeAreas = areas.filter((a) => a.isActive).length;

  // Handlers
  const handleCreateDistrict = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newDistrictName.trim()) return;
    onAddDistrict(newDistrictName.trim());
    setNewDistrictName('');
  };

  const handleCreateTehsil = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newTehsilName.trim()) return;
    onAddTehsil(newTehsilName.trim(), newTehsilDistrictId);
    setNewTehsilName('');
  };

  const handleCreateArea = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newAreaName.trim()) return;
    onAddArea(newAreaName.trim(), newAreaTehsilId);
    setNewAreaName('');
  };

  const handleSaveEdit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingItem || !editingItem.name.trim()) return;

    if (editingItem.type === 'district') {
      onUpdateDistrict(editingItem.id, { name: editingItem.name.trim() });
    } else if (editingItem.type === 'tehsil') {
      onUpdateTehsil(editingItem.id, { name: editingItem.name.trim(), districtId: editingItem.parentId });
    } else if (editingItem.type === 'area') {
      onUpdateArea(editingItem.id, { name: editingItem.name.trim(), tehsilId: editingItem.parentId });
    }
    setEditingItem(null);
  };

  return (
    <div className="bg-white border border-cyan-200 rounded-3xl p-6 md:p-8 shadow-sm">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100 mb-6">
        <div>
          <div className="flex items-center gap-2">
            <h2 className="text-xl font-black text-slate-900">Location Wise Settings System</h2>
            <span className="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-100 text-cyan-800 border border-cyan-300">
              Division → District → Tehsil → Area
            </span>
          </div>
          <p className="text-slate-500 text-xs mt-1 font-medium">
            Manage administrative hierarchy for Sargodha Division. Dependent dropdowns in posting forms and location-based marketplace filtering automatically use these locations.
          </p>
        </div>

        {/* Quick Stats Pill */}
        <div className="flex items-center gap-2 shrink-0">
          <span className="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700">
            Districts: <strong className="text-cyan-800">{activeDistricts}/{districts.length}</strong>
          </span>
          <span className="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700">
            Tehsils: <strong className="text-blue-800">{activeTehsils}/{tehsils.length}</strong>
          </span>
          <span className="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700">
            Areas: <strong className="text-emerald-800">{activeAreas}/{areas.length}</strong>
          </span>
        </div>
      </div>

      {/* Tabs */}
      <div className="flex flex-wrap gap-2 mb-6">
        <button
          onClick={() => setActiveSubTab('hierarchy')}
          className={`px-4 py-2 rounded-xl font-bold text-xs flex items-center gap-1.5 border transition-all ${
            activeSubTab === 'hierarchy'
              ? 'bg-cyan-50 text-cyan-800 border-cyan-300 shadow-sm'
              : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'
          }`}
        >
          <Layers className="w-3.5 h-3.5" />
          <span>Hierarchy Tree View</span>
        </button>

        <button
          onClick={() => setActiveSubTab('districts')}
          className={`px-4 py-2 rounded-xl font-bold text-xs flex items-center gap-1.5 border transition-all ${
            activeSubTab === 'districts'
              ? 'bg-cyan-50 text-cyan-800 border-cyan-300 shadow-sm'
              : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'
          }`}
        >
          <Building className="w-3.5 h-3.5" />
          <span>Districts ({districts.length})</span>
        </button>

        <button
          onClick={() => setActiveSubTab('tehsils')}
          className={`px-4 py-2 rounded-xl font-bold text-xs flex items-center gap-1.5 border transition-all ${
            activeSubTab === 'tehsils'
              ? 'bg-blue-50 text-blue-800 border-blue-300 shadow-sm'
              : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'
          }`}
        >
          <MapPin className="w-3.5 h-3.5" />
          <span>Tehsils ({tehsils.length})</span>
        </button>

        <button
          onClick={() => setActiveSubTab('areas')}
          className={`px-4 py-2 rounded-xl font-bold text-xs flex items-center gap-1.5 border transition-all ${
            activeSubTab === 'areas'
              ? 'bg-emerald-50 text-emerald-800 border-emerald-300 shadow-sm'
              : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'
          }`}
        >
          <MapPin className="w-3.5 h-3.5" />
          <span>Areas / Hubs ({areas.length})</span>
        </button>
      </div>

      {/* VIEW 1: HIERARCHY TREE VIEW */}
      {activeSubTab === 'hierarchy' && (
        <div className="space-y-6">
          <div className="p-4 rounded-2xl bg-cyan-50/50 border border-cyan-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <span className="text-[11px] font-bold text-cyan-800 uppercase tracking-wider block">Target Division</span>
              <h3 className="text-lg font-black text-slate-900 flex items-center gap-2 mt-0.5">
                <span>📍</span> Sargodha Division (Punjab)
              </h3>
            </div>
            <div className="text-xs text-slate-600 font-medium">
              Includes 4 Districts: <strong>Sargodha (7 Tehsils)</strong>, <strong>Khushab</strong>, <strong>Mianwali</strong>, <strong>Bhakkar</strong>
            </div>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Step 1: Select District */}
            <div className="p-4 rounded-2xl bg-slate-50 border border-slate-200">
              <div className="flex items-center justify-between mb-3">
                <span className="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                  <Building className="w-4 h-4 text-cyan-600" /> 1. Select District
                </span>
                <span className="text-[10px] text-slate-400 font-mono">{districts.length} districts</span>
              </div>

              <div className="space-y-2">
                {districts.map((d) => {
                  const districtTehsils = tehsils.filter((t) => t.districtId === d.id);
                  const isSelected = selectedDistrictId === d.id;
                  return (
                    <button
                      key={d.id}
                      onClick={() => {
                        setSelectedDistrictId(d.id);
                        const firstTehsil = districtTehsils[0];
                        if (firstTehsil) setSelectedTehsilId(firstTehsil.id);
                      }}
                      className={`w-full p-3 rounded-xl text-left text-xs font-bold flex items-center justify-between border transition-all ${
                        isSelected
                          ? 'bg-white text-cyan-800 border-cyan-400 shadow-sm'
                          : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300'
                      }`}
                    >
                      <div className="flex items-center gap-2">
                        <span className={`w-2 h-2 rounded-full ${d.isActive ? 'bg-emerald-500' : 'bg-slate-300'}`} />
                        <span>{d.name}</span>
                      </div>
                      <div className="flex items-center gap-1.5 text-slate-400 text-[11px]">
                        <span>{districtTehsils.length} tehsils</span>
                        <ChevronRight className="w-3.5 h-3.5" />
                      </div>
                    </button>
                  );
                })}
              </div>
            </div>

            {/* Step 2: Tehsils in Selected District */}
            <div className="p-4 rounded-2xl bg-slate-50 border border-slate-200">
              <div className="flex items-center justify-between mb-3">
                <span className="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                  <MapPin className="w-4 h-4 text-blue-600" /> 2. Tehsils in {districts.find((d) => d.id === selectedDistrictId)?.name}
                </span>
                <span className="text-[10px] text-slate-400 font-mono">
                  {tehsils.filter((t) => t.districtId === selectedDistrictId).length} tehsils
                </span>
              </div>

              <div className="space-y-2 max-h-96 overflow-y-auto pr-1">
                {tehsils
                  .filter((t) => t.districtId === selectedDistrictId)
                  .map((t) => {
                    const tehsilAreas = areas.filter((a) => a.tehsilId === t.id);
                    const isSelected = selectedTehsilId === t.id;
                    return (
                      <button
                        key={t.id}
                        onClick={() => setSelectedTehsilId(t.id)}
                        className={`w-full p-3 rounded-xl text-left text-xs font-bold flex items-center justify-between border transition-all ${
                          isSelected
                            ? 'bg-white text-blue-800 border-blue-400 shadow-sm'
                            : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300'
                        }`}
                      >
                        <div className="flex items-center gap-2">
                          <span className={`w-2 h-2 rounded-full ${t.isActive ? 'bg-emerald-500' : 'bg-slate-300'}`} />
                          <span>{t.name}</span>
                        </div>
                        <div className="flex items-center gap-1.5 text-slate-400 text-[11px]">
                          <span>{tehsilAreas.length} areas</span>
                          <ChevronRight className="w-3.5 h-3.5" />
                        </div>
                      </button>
                    );
                  })}
              </div>
            </div>

            {/* Step 3: Areas in Selected Tehsil */}
            <div className="p-4 rounded-2xl bg-slate-50 border border-slate-200">
              <div className="flex items-center justify-between mb-3">
                <span className="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                  <MapPin className="w-4 h-4 text-emerald-600" /> 3. Areas in {tehsils.find((t) => t.id === selectedTehsilId)?.name}
                </span>
                <span className="text-[10px] text-slate-400 font-mono">
                  {areas.filter((a) => a.tehsilId === selectedTehsilId).length} areas
                </span>
              </div>

              <div className="space-y-2 max-h-96 overflow-y-auto pr-1">
                {areas
                  .filter((a) => a.tehsilId === selectedTehsilId)
                  .map((a) => (
                    <div
                      key={a.id}
                      className="p-2.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold flex items-center justify-between"
                    >
                      <div className="flex items-center gap-2">
                        <span className={`w-2 h-2 rounded-full ${a.isActive ? 'bg-emerald-500' : 'bg-slate-300'}`} />
                        <span className="text-slate-800">{a.name}</span>
                      </div>
                      <button
                        onClick={() => onUpdateArea(a.id, { isActive: !a.isActive })}
                        className={`text-[10px] font-bold px-2 py-0.5 rounded-lg border ${
                          a.isActive ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200'
                        }`}
                      >
                        {a.isActive ? 'Active' : 'Disabled'}
                      </button>
                    </div>
                  ))}

                {areas.filter((a) => a.tehsilId === selectedTehsilId).length === 0 && (
                  <div className="text-center py-6 text-slate-400 text-xs">
                    No custom areas added yet for this Tehsil.
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      )}

      {/* VIEW 2: DISTRICTS LIST & ADD/EDIT */}
      {activeSubTab === 'districts' && (
        <div className="space-y-6">
          {/* Add District Bar */}
          <form onSubmit={handleCreateDistrict} className="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row gap-3">
            <input
              type="text"
              placeholder="Enter new District name (e.g. Khushab, Mianwali...)"
              value={newDistrictName}
              onChange={(e) => setNewDistrictName(e.target.value)}
              className="flex-1 px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-900"
              required
            />
            <button
              type="submit"
              className="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 shrink-0 shadow-sm"
            >
              <Plus className="w-4 h-4" /> Add District
            </button>
          </form>

          {/* District Table */}
          <div className="overflow-x-auto">
            <table className="w-full text-xs text-left">
              <thead>
                <tr className="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                  <th className="py-3 px-3">District Name</th>
                  <th className="py-3 px-3">Division</th>
                  <th className="py-3 px-3">Associated Tehsils</th>
                  <th className="py-3 px-3">Status</th>
                  <th className="py-3 px-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 font-medium">
                {districts.map((d) => {
                  const associatedTehsils = tehsils.filter((t) => t.districtId === d.id);
                  return (
                    <tr key={d.id} className="hover:bg-slate-50/80">
                      <td className="py-3 px-3 font-bold text-slate-900">{d.name}</td>
                      <td className="py-3 px-3 text-slate-500">Sargodha Division</td>
                      <td className="py-3 px-3">
                        <span className="badge px-2 py-1 rounded-md bg-blue-50 text-blue-800 font-semibold border border-blue-200">
                          {associatedTehsils.length} Tehsils
                        </span>
                      </td>
                      <td className="py-3 px-3">
                        <button
                          onClick={() => onUpdateDistrict(d.id, { isActive: !d.isActive })}
                          className={`px-2.5 py-1 rounded-lg text-[10px] font-bold border ${
                            d.isActive ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-500 border-slate-200'
                          }`}
                        >
                          {d.isActive ? 'Active' : 'Disabled'}
                        </button>
                      </td>
                      <td className="py-3 px-3 text-right">
                        <div className="flex items-center justify-end gap-1.5">
                          <button
                            onClick={() => setEditingItem({ type: 'district', id: d.id, name: d.name })}
                            className="p-1.5 rounded-lg text-slate-400 hover:text-cyan-800 hover:bg-cyan-50"
                            title="Edit District"
                          >
                            <Edit2 className="w-3.5 h-3.5" />
                          </button>
                          <button
                            onClick={() => {
                              if (confirm(`Delete district "${d.name}" and its associated tehsils?`)) {
                                onDeleteDistrict(d.id);
                              }
                            }}
                            className="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50"
                            title="Delete District"
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* VIEW 3: TEHSILS LIST & ASSIGNMENT */}
      {activeSubTab === 'tehsils' && (
        <div className="space-y-6">
          {/* Add Tehsil Bar with District Assignment */}
          <form onSubmit={handleCreateTehsil} className="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col md:flex-row gap-3">
            <div className="w-full md:w-56 shrink-0">
              <label className="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Assign to District</label>
              <select
                value={newTehsilDistrictId}
                onChange={(e) => setNewTehsilDistrictId(Number(e.target.value))}
                className="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-900"
              >
                {districts.map((d) => (
                  <option key={d.id} value={d.id}>{d.name} District</option>
                ))}
              </select>
            </div>

            <div className="flex-1">
              <label className="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tehsil Name</label>
              <input
                type="text"
                placeholder="Enter Tehsil name (e.g. Sillanwali, Bhalwal, Bhera, Kot Momin...)"
                value={newTehsilName}
                onChange={(e) => setNewTehsilName(e.target.value)}
                className="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-900"
                required
              />
            </div>

            <div className="flex items-end">
              <button
                type="submit"
                className="w-full md:w-auto px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm"
              >
                <Plus className="w-4 h-4" /> Add Tehsil
              </button>
            </div>
          </form>

          {/* Tehsil Table */}
          <div className="overflow-x-auto">
            <table className="w-full text-xs text-left">
              <thead>
                <tr className="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                  <th className="py-3 px-3">Tehsil Name</th>
                  <th className="py-3 px-3">Assigned District</th>
                  <th className="py-3 px-3">Areas Count</th>
                  <th className="py-3 px-3">Status</th>
                  <th className="py-3 px-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 font-medium">
                {tehsils.map((t) => {
                  const districtObj = districts.find((d) => d.id === t.districtId);
                  const areasInTehsil = areas.filter((a) => a.tehsilId === t.id);
                  return (
                    <tr key={t.id} className="hover:bg-slate-50/80">
                      <td className="py-3 px-3 font-bold text-slate-900 flex items-center gap-2">
                        <MapPin className="w-3.5 h-3.5 text-blue-600" />
                        <span>{t.name}</span>
                      </td>
                      <td className="py-3 px-3 text-slate-700 font-semibold">{districtObj?.name || 'Unknown'} District</td>
                      <td className="py-3 px-3">
                        <span className="badge px-2 py-1 rounded-md bg-emerald-50 text-emerald-800 font-semibold border border-emerald-200">
                          {areasInTehsil.length} Areas
                        </span>
                      </td>
                      <td className="py-3 px-3">
                        <button
                          onClick={() => onUpdateTehsil(t.id, { isActive: !t.isActive })}
                          className={`px-2.5 py-1 rounded-lg text-[10px] font-bold border ${
                            t.isActive ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-500 border-slate-200'
                          }`}
                        >
                          {t.isActive ? 'Active' : 'Disabled'}
                        </button>
                      </td>
                      <td className="py-3 px-3 text-right">
                        <div className="flex items-center justify-end gap-1.5">
                          <button
                            onClick={() => setEditingItem({ type: 'tehsil', id: t.id, name: t.name, parentId: t.districtId })}
                            className="p-1.5 rounded-lg text-slate-400 hover:text-blue-800 hover:bg-blue-50"
                            title="Edit Tehsil"
                          >
                            <Edit2 className="w-3.5 h-3.5" />
                          </button>
                          <button
                            onClick={() => {
                              if (confirm(`Delete tehsil "${t.name}" and its areas?`)) {
                                onDeleteTehsil(t.id);
                              }
                            }}
                            className="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50"
                            title="Delete Tehsil"
                          >
                            <Trash2 className="w-3.5 h-3.5" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* VIEW 4: AREAS LIST & ASSIGNMENT */}
      {activeSubTab === 'areas' && (
        <div className="space-y-6">
          {/* Add Area Bar with Tehsil Assignment */}
          <form onSubmit={handleCreateArea} className="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col md:flex-row gap-3">
            <div className="w-full md:w-64 shrink-0">
              <label className="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Assign to Tehsil</label>
              <select
                value={newAreaTehsilId}
                onChange={(e) => setNewAreaTehsilId(Number(e.target.value))}
                className="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-900"
              >
                {tehsils.map((t) => {
                  const dist = districts.find((d) => d.id === t.districtId);
                  return (
                    <option key={t.id} value={t.id}>
                      {t.name} (District: {dist?.name || 'Sargodha'})
                    </option>
                  );
                })}
              </select>
            </div>

            <div className="flex-1">
              <label className="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Area / Landmark Name</label>
              <input
                type="text"
                placeholder="Enter Area name (e.g. Shaheenabad, Satellite Town, Canal Colony...)"
                value={newAreaName}
                onChange={(e) => setNewAreaName(e.target.value)}
                className="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-900"
                required
              />
            </div>

            <div className="flex items-end">
              <button
                type="submit"
                className="w-full md:w-auto px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm"
              >
                <Plus className="w-4 h-4" /> Add Area
              </button>
            </div>
          </form>

          {/* Search Filter */}
          <div className="relative">
            <Search className="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              type="text"
              placeholder="Search area names..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-medium"
            />
          </div>

          {/* Area Table */}
          <div className="overflow-x-auto">
            <table className="w-full text-xs text-left">
              <thead>
                <tr className="border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                  <th className="py-3 px-3">Area Name</th>
                  <th className="py-3 px-3">Tehsil</th>
                  <th className="py-3 px-3">District</th>
                  <th className="py-3 px-3">Status</th>
                  <th className="py-3 px-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100 font-medium">
                {areas
                  .filter((a) => !searchQuery || a.name.toLowerCase().includes(searchQuery.toLowerCase()))
                  .map((a) => {
                    const tehsilObj = tehsils.find((t) => t.id === a.tehsilId);
                    const districtObj = tehsilObj ? districts.find((d) => d.id === tehsilObj.districtId) : null;
                    return (
                      <tr key={a.id} className="hover:bg-slate-50/80">
                        <td className="py-3 px-3 font-bold text-slate-900">{a.name}</td>
                        <td className="py-3 px-3 text-blue-800 font-semibold">{tehsilObj?.name || 'Unknown'}</td>
                        <td className="py-3 px-3 text-slate-500">{districtObj?.name || 'Sargodha'}</td>
                        <td className="py-3 px-3">
                          <button
                            onClick={() => onUpdateArea(a.id, { isActive: !a.isActive })}
                            className={`px-2.5 py-1 rounded-lg text-[10px] font-bold border ${
                              a.isActive ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-slate-100 text-slate-500 border-slate-200'
                            }`}
                          >
                            {a.isActive ? 'Active' : 'Disabled'}
                          </button>
                        </td>
                        <td className="py-3 px-3 text-right">
                          <div className="flex items-center justify-end gap-1.5">
                            <button
                              onClick={() => setEditingItem({ type: 'area', id: a.id, name: a.name, parentId: a.tehsilId })}
                              className="p-1.5 rounded-lg text-slate-400 hover:text-emerald-800 hover:bg-emerald-50"
                              title="Edit Area"
                            >
                              <Edit2 className="w-3.5 h-3.5" />
                            </button>
                            <button
                              onClick={() => {
                                if (confirm(`Delete area "${a.name}"?`)) {
                                  onDeleteArea(a.id);
                                }
                              }}
                              className="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50"
                              title="Delete Area"
                            >
                              <Trash2 className="w-3.5 h-3.5" />
                            </button>
                          </div>
                        </td>
                      </tr>
                    );
                  })}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Edit Modal Dialog */}
      {editingItem && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
          <div className="bg-white rounded-3xl p-6 w-full max-w-md border border-cyan-200 shadow-2xl">
            <div className="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
              <h4 className="font-bold text-sm text-slate-900">
                Edit {editingItem.type.charAt(0).toUpperCase() + editingItem.type.slice(1)}
              </h4>
              <button onClick={() => setEditingItem(null)} className="p-1 rounded-lg text-slate-400 hover:text-slate-700">
                <X className="w-4 h-4" />
              </button>
            </div>

            <form onSubmit={handleSaveEdit} className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Name</label>
                <input
                  type="text"
                  value={editingItem.name}
                  onChange={(e) => setEditingItem({ ...editingItem, name: e.target.value })}
                  className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900"
                  required
                />
              </div>

              {editingItem.type === 'tehsil' && (
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Parent District</label>
                  <select
                    value={editingItem.parentId}
                    onChange={(e) => setEditingItem({ ...editingItem, parentId: Number(e.target.value) })}
                    className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900"
                  >
                    {districts.map((d) => (
                      <option key={d.id} value={d.id}>{d.name} District</option>
                    ))}
                  </select>
                </div>
              )}

              {editingItem.type === 'area' && (
                <div>
                  <label className="block text-xs font-bold text-slate-700 mb-1">Parent Tehsil</label>
                  <select
                    value={editingItem.parentId}
                    onChange={(e) => setEditingItem({ ...editingItem, parentId: Number(e.target.value) })}
                    className="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900"
                  >
                    {tehsils.map((t) => (
                      <option key={t.id} value={t.id}>{t.name} (District: {districts.find((d) => d.id === t.districtId)?.name})</option>
                    ))}
                  </select>
                </div>
              )}

              <div className="flex items-center justify-end gap-2 pt-2">
                <button
                  type="button"
                  onClick={() => setEditingItem(null)}
                  className="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 rounded-xl text-xs font-bold bg-cyan-600 hover:bg-cyan-500 text-white shadow-sm flex items-center gap-1.5"
                >
                  <Check className="w-3.5 h-3.5" /> Save Changes
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
