import { Division, District, Tehsil, AreaLocation } from '../types';

export const INITIAL_DIVISIONS: Division[] = [
  { id: 1, name: 'Sargodha Division', isActive: true },
];

export const INITIAL_DISTRICTS: District[] = [
  { id: 1, divisionId: 1, name: 'Sargodha', isActive: true },
  { id: 2, divisionId: 1, name: 'Khushab', isActive: true },
  { id: 3, divisionId: 1, name: 'Mianwali', isActive: true },
  { id: 4, divisionId: 1, name: 'Bhakkar', isActive: true },
];

export const INITIAL_TEHSILS: Tehsil[] = [
  // 7 Tehsils of Sargodha District
  { id: 1, districtId: 1, name: 'Sargodha', isActive: true },
  { id: 2, districtId: 1, name: 'Bhalwal', isActive: true },
  { id: 3, districtId: 1, name: 'Bhera', isActive: true },
  { id: 4, districtId: 1, name: 'Kot Momin', isActive: true },
  { id: 5, districtId: 1, name: 'Sahiwal', isActive: true },
  { id: 6, districtId: 1, name: 'Shahpur', isActive: true },
  { id: 7, districtId: 1, name: 'Sillanwali', isActive: true },

  // Tehsils of Khushab District
  { id: 8, districtId: 2, name: 'Khushab', isActive: true },
  { id: 9, districtId: 2, name: 'Noorpur Thal', isActive: true },
  { id: 10, districtId: 2, name: 'Quaidabad', isActive: true },
  { id: 11, districtId: 2, name: 'Naushera (Soon Valley)', isActive: true },

  // Tehsils of Mianwali District
  { id: 12, districtId: 3, name: 'Mianwali', isActive: true },
  { id: 13, districtId: 3, name: 'Isa Khel', isActive: true },
  { id: 14, districtId: 3, name: 'Piplan', isActive: true },

  // Tehsils of Bhakkar District
  { id: 15, districtId: 4, name: 'Bhakkar', isActive: true },
  { id: 16, districtId: 4, name: 'Darya Khan', isActive: true },
  { id: 17, districtId: 4, name: 'Kallurkot', isActive: true },
  { id: 18, districtId: 4, name: 'Mankera', isActive: true },
];

export const INITIAL_AREAS: AreaLocation[] = [
  // Sillanwali Tehsil (id: 7) - Key Areas
  { id: 1, tehsilId: 7, name: 'Shaheenabad', isActive: true },
  { id: 2, tehsilId: 7, name: 'Sillanwali City', isActive: true },
  { id: 3, tehsilId: 7, name: 'Main Mandi Bazaar & Citrus Belt', isActive: true },
  { id: 4, tehsilId: 7, name: 'Canal Colony', isActive: true },
  { id: 5, tehsilId: 7, name: 'Farooqabad', isActive: true },

  // Sargodha Tehsil (id: 1) - Key Areas
  { id: 6, tehsilId: 1, name: 'Satellite Town', isActive: true },
  { id: 7, tehsilId: 1, name: 'University Road', isActive: true },
  { id: 8, tehsilId: 1, name: 'Trust Plaza / Kutchery Bazaar', isActive: true },
  { id: 9, tehsilId: 1, name: 'Fatima Jinnah Road', isActive: true },
  { id: 10, tehsilId: 1, name: 'Club Road', isActive: true },
  { id: 11, tehsilId: 1, name: 'Civil Lines', isActive: true },
  { id: 12, tehsilId: 1, name: 'Canal Colony Dairy Belt', isActive: true },
  { id: 13, tehsilId: 1, name: 'Remount Depot', isActive: true },

  // Bhalwal Tehsil (id: 2)
  { id: 14, tehsilId: 2, name: 'Bhalwal City', isActive: true },
  { id: 15, tehsilId: 2, name: 'Station Road Grain Market', isActive: true },
  { id: 16, tehsilId: 2, name: 'Chak 10 NB', isActive: true },

  // Bhera Tehsil (id: 3)
  { id: 17, tehsilId: 3, name: 'Historical Walled City', isActive: true },
  { id: 18, tehsilId: 3, name: 'Circular Road', isActive: true },
  { id: 19, tehsilId: 3, name: 'Motorway Interchange Area', isActive: true },

  // Kot Momin Tehsil (id: 4)
  { id: 20, tehsilId: 4, name: 'Kot Momin Main Bazaar', isActive: true },
  { id: 21, tehsilId: 4, name: 'Kinnow Citrus Market', isActive: true },
  { id: 22, tehsilId: 4, name: 'M-2 Motorway Interchange Area', isActive: true },

  // Sahiwal Tehsil (id: 5)
  { id: 23, tehsilId: 5, name: 'Sahiwal Tehsil City', isActive: true },
  { id: 24, tehsilId: 5, name: 'Faruka Town', isActive: true },
  { id: 25, tehsilId: 5, name: 'Jhelum River Agriculture Belt', isActive: true },

  // Shahpur Tehsil (id: 6)
  { id: 26, tehsilId: 6, name: 'Shahpur Saddar', isActive: true },
  { id: 27, tehsilId: 6, name: 'Shahpur City', isActive: true },
  { id: 28, tehsilId: 6, name: 'Jhelum River Agriculture Belt', isActive: true },

  // Khushab Tehsil (id: 8)
  { id: 29, tehsilId: 8, name: 'Khushab City', isActive: true },
  { id: 30, tehsilId: 8, name: 'Jauharabad', isActive: true },

  // Noorpur Thal (id: 9)
  { id: 31, tehsilId: 9, name: 'Noorpur Thal Town', isActive: true },

  // Mianwali Tehsil (id: 12)
  { id: 32, tehsilId: 12, name: 'Mianwali City Center', isActive: true },
  { id: 33, tehsilId: 12, name: 'Ballokhel Road', isActive: true },

  // Bhakkar Tehsil (id: 15)
  { id: 34, tehsilId: 15, name: 'Bhakkar City', isActive: true },
  { id: 35, tehsilId: 15, name: 'Chishti Town', isActive: true },
];
