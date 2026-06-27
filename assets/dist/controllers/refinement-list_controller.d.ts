import { Controller } from '@hotwired/stimulus';
export default class extends Controller {
    static values: {
        limit: NumberConstructor;
        isShowingMore: {
            type: BooleanConstructor;
            default: boolean;
        };
        showMoreLabel: StringConstructor;
        showLessLabel: StringConstructor;
        isSearching: {
            type: BooleanConstructor;
            default: boolean;
        };
        valueType: {
            type: StringConstructor;
            default: string;
        };
    };
    isShowingMoreValue: boolean;
    showMoreLabelValue: string;
    showLessLabelValue: string;
    isSearchingValue: boolean;
    valueTypeValue: string;
    limitValue: number;
    static targets: string[];
    hasToggleTarget: boolean;
    toggleTarget: HTMLButtonElement;
    hasSearchInputTarget: boolean;
    searchInputTarget: HTMLInputElement;
    listTarget: HTMLUListElement;
    itemTargets: HTMLElement[];
    labelTargets: HTMLElement[];
    countTargets: HTMLElement[];
    hasSortTarget: boolean;
    sortTarget: HTMLSelectElement;
    sortOptionTargets: HTMLOptionElement[];
    mutationObserver: MutationObserver;
    initialize(): void;
    connect(): void;
    private handleMutation;
    isShowingMoreValueChanged(): void;
    toggleShowMore(): void;
    search(): void;
    sort(): void;
    private updateToggleLabel;
    private configureSortOptions;
    private updateLimitedItems;
    private getValueType;
    private guessValueType;
    private compareValues;
    private getLabel;
    private getCount;
    private parseNumber;
    private parseDate;
    private normalize;
    disconnect(): void;
}
