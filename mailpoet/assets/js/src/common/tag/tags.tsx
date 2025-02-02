import { ReactNode } from 'react';
import { Tag } from './tag';
import { Tooltip } from '../tooltip/tooltip';
import { MailPoet } from '../../mailpoet';

type Segment = {
  name: string;
  id?: string;
};

type Props = {
  children?: ReactNode;
  dimension?: 'large';
  segments?: Segment[];
  strings?: string[];
};

function Tags({ children, dimension, segments, strings }: Props) {
  return (
    <div className="mailpoet-tags">
      {children}
      {segments &&
        segments.map((segment) => {
          const tag = (
            <Tag key={segment.name} dimension={dimension} variant="list">
              {segment.name}
            </Tag>
          );
          if (!segment.id) {
            return tag;
          }
          const randomId = Math.random().toString(36).substring(2, 15);
          const tooltipId = `segment-tooltip-${randomId}`;

          return (
            <div key={randomId}>
              <Tooltip id={tooltipId} place="top">
                {MailPoet.I18n.t('viewFilteredSubscribersMessage').replace(
                  '%1$s',
                  segment.name,
                )}
              </Tooltip>
              <a
                data-tip=""
                data-for={tooltipId}
                href={`admin.php?page=mailpoet-subscribers#/filter[segment=${segment.id}]`}
              >
                {tag}
              </a>
            </div>
          );
        })}
      {strings &&
        strings.map((string) => (
          <Tag key={string} dimension={dimension} variant="list">
            {string}
          </Tag>
        ))}
    </div>
  );
}

export { Tags };
