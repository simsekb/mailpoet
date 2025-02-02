import { Button, ToolbarItem } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { __, _x } from '@wordpress/i18n';
import { plus } from '@wordpress/icons';
import { store } from '../../store';

// See:
//   https://github.com/WordPress/gutenberg/blob/5caeae34b3fb303761e3b9432311b26f4e5ea3a6/packages/edit-post/src/components/header/header-toolbar/index.js
//   https://github.com/WordPress/gutenberg/blob/9601a33e30ba41bac98579c8d822af63dd961488/packages/edit-navigation/src/components/header/inserter-toggle.js

export function InserterToggle(): JSX.Element {
  const { isInserterOpened, showIconLabels } = useSelect(
    (select) => ({
      isInserterOpened: select(store).isInserterSidebarOpened(),
      showIconLabels: select(store).isFeatureActive('showIconLabels'),
    }),
    [],
  );

  const { toggleInserterSidebar } = useDispatch(store);

  return (
    <ToolbarItem
      as={Button}
      className="edit-post-header-toolbar__inserter-toggle"
      variant="primary"
      isPressed={isInserterOpened}
      onMouseDown={(event) => event.preventDefault()}
      onClick={toggleInserterSidebar}
      icon={plus}
      label={_x(
        'Toggle step inserter',
        'Generic label for step inserter button',
      )}
      showTooltip={!showIconLabels}
    >
      {showIconLabels && (!isInserterOpened ? __('Add') : __('Close'))}
    </ToolbarItem>
  );
}
