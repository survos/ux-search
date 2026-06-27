import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
  static values = {
    limit: Number,
    isShowingMore: {
      type: Boolean,
      default: false,
    },
    showMoreLabel: String,
    showLessLabel: String,
    isSearching: {
      type: Boolean,
      default: false,
    },
    valueType: {
      type: String,
      default: 'auto',
    },
  };

  declare isShowingMoreValue: boolean;
  declare showMoreLabelValue: string;
  declare showLessLabelValue: string;
  declare isSearchingValue: boolean;
  declare valueTypeValue: string;
  declare limitValue: number;

  static targets = ['toggle', 'searchInput', 'list', 'item', 'label', 'count', 'sort', 'sortOption'];

  declare hasToggleTarget: boolean;
  declare toggleTarget: HTMLButtonElement;
  declare hasSearchInputTarget: boolean;
  declare searchInputTarget: HTMLInputElement;
  declare listTarget: HTMLUListElement;
  declare itemTargets: HTMLElement[];
  declare labelTargets: HTMLElement[];
  declare countTargets: HTMLElement[];
  declare hasSortTarget: boolean;
  declare sortTarget: HTMLSelectElement;
  declare sortOptionTargets: HTMLOptionElement[];

  mutationObserver!: MutationObserver;

  initialize() {
    this.mutationObserver = new MutationObserver(this.handleMutation);
  }

  connect() {
    this.mutationObserver.observe(this.element, {
      childList: true,
    });
    this.configureSortOptions();
    this.sort();
  }

  private handleMutation = () => {
    this.updateToggleLabel();
  };

  isShowingMoreValueChanged() {
    this.updateToggleLabel();
  }

  toggleShowMore() {
    this.isShowingMoreValue = !this.isShowingMoreValue;
  }

  search() {
    if (!this.hasSearchInputTarget) return;

    const query = this.normalize(this.searchInputTarget.value);
    this.isSearchingValue = query.length > 0;

    this.itemTargets.forEach((item, index) => {
      const label = this.labelTargets[index]?.textContent ?? '';
      item.classList.toggle('ux-search-refinement-list__item--hidden', !this.normalize(label).includes(query));
    });
  }

  sort() {
    if (!this.hasSortTarget) return;

    const sortValue = this.sortTarget.value;
    const valueType = this.getValueType();
    const items = [...this.itemTargets];

    items.sort((itemA, itemB) => {
      const indexA = this.itemTargets.indexOf(itemA);
      const indexB = this.itemTargets.indexOf(itemB);

      if ('count_asc' === sortValue || 'count_desc' === sortValue) {
        const countA = this.getCount(indexA);
        const countB = this.getCount(indexB);

        return 'count_asc' === sortValue ? countA - countB : countB - countA;
      }

      return this.compareValues(this.getLabel(indexA), this.getLabel(indexB), valueType, sortValue.endsWith('_desc'));
    });

    items.forEach((item) => this.listTarget.append(item));
    this.updateLimitedItems();
    this.search();
  }

  /**
   * Update the toggle button label based on current state
   * @private
   */
  private updateToggleLabel() {
    if (!this.hasToggleTarget) return;
    this.toggleTarget.innerHTML = this.isShowingMoreValue ? this.showLessLabelValue : this.showMoreLabelValue;
  }

  private configureSortOptions() {
    if (!this.hasSortTarget) return;

    const valueType = this.getValueType();

    this.sortOptionTargets.forEach((option) => {
      option.hidden = option.dataset.valueType !== valueType;
    });

    if (this.sortTarget.selectedOptions[0]?.hidden) {
      this.sortTarget.value = 'count_desc';
    }
  }

  private updateLimitedItems() {
    this.itemTargets.forEach((item, index) => {
      item.classList.toggle('ux-search-refinement-list__item--exceed-limit', index >= this.limitValue);
    });
  }

  private getValueType() {
    const configuredValueType = this.normalize(this.valueTypeValue || 'auto');

    if (['date', 'number', 'string'].includes(configuredValueType)) {
      return configuredValueType;
    }

    return this.guessValueType();
  }

  private guessValueType() {
    const labels = this.labelTargets.map((label) => (label.textContent ?? '').trim()).filter((label) => label.length > 0);

    if (labels.length > 0 && labels.every((label) => /^\d{4}$/.test(label))) {
      return 'date';
    }

    if (labels.length > 0 && labels.every((label) => this.parseNumber(label) !== null)) {
      return 'number';
    }

    if (labels.length > 0 && labels.every((label) => this.parseDate(label) !== null)) {
      return 'date';
    }

    return 'string';
  }

  private compareValues(valueA: string, valueB: string, valueType: string, descending: boolean) {
    let comparison: number;

    if ('date' === valueType) {
      comparison = (this.parseDate(valueA) ?? 0) - (this.parseDate(valueB) ?? 0);
    } else if ('number' === valueType) {
      comparison = (this.parseNumber(valueA) ?? 0) - (this.parseNumber(valueB) ?? 0);
    } else {
      comparison = valueA.localeCompare(valueB, undefined, { numeric: true, sensitivity: 'base' });
    }

    return descending ? comparison * -1 : comparison;
  }

  private getLabel(index: number) {
    return this.labelTargets[index]?.textContent?.trim() ?? '';
  }

  private getCount(index: number) {
    return Number.parseFloat((this.countTargets[index]?.textContent ?? '').replace(/[^\d.-]/g, '')) || 0;
  }

  private parseNumber(value: string) {
    const normalizedValue = value.trim().replace(/,/g, '');

    if (!/^-?\d+(\.\d+)?$/.test(normalizedValue)) {
      return null;
    }

    return Number.parseFloat(normalizedValue);
  }

  private parseDate(value: string) {
    const normalizedValue = value.trim();

    if (/^\d{4}$/.test(normalizedValue)) {
      return Date.UTC(Number.parseInt(normalizedValue, 10), 0, 1);
    }

    const timestamp = Date.parse(normalizedValue);

    return Number.isNaN(timestamp) ? null : timestamp;
  }

  private normalize(value: string) {
    return value.trim().toLocaleLowerCase();
  }

  disconnect() {
    this.mutationObserver.disconnect();
  }
}
