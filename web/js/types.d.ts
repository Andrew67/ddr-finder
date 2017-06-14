/*! ddr-finder | https://github.com/Andrew67/ddr-finder/blob/master/LICENSE */
// Definitions for returned data from API, see https://github.com/Andrew67/ddr-finder/wiki/API-Description
// API Level: 2.0

interface ArcadeLocation {
    id: number;
    src: string;
    sid: string;
    name: string;
    city: string;
    lat: number;
    lng: number;
    hasDDR: number;
    distance: number;
}

interface DataSource {
    name: string;
    infoURL: string;
    mInfoURL: string;
    hasDDR: boolean;
}

interface APIData {
    error?: string;
    errorCode?: number;
    sources: {[shortName: string]: DataSource};
    locations: ArcadeLocation[];
}