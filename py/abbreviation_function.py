"""
  The following functions have been obtained by scispacy in order to extract all the acronyms within a biological
  text.
  DOID:                   10.18653/v1/W19-5034
  WEBSITE:                https://allenai.github.io/scispacy/
  ALGORITHM DESCRIPTION:  https://psb.stanford.edu/psb-online/proceedings/psb03/schwartz.pdf
"""

from   typing         import Tuple, List, Optional, Set, Dict
from   collections    import defaultdict
from   spacy.tokens   import Span, Doc
from   spacy.matcher  import Matcher
from   spacy.language import Language
import re


# FUNCTION 1
def filter_matches(matcher_output: List[Tuple[int, int, int]], doc: Doc) -> List[Tuple[Span, Span]]:
    # Filter into two cases:
    # 1. <Short Form> ( <Long Form> )
    # 2. <Long Form> (<Short Form>) [this case is most common].
    candidates = []
    for match in matcher_output:
        start = match[1]
        end   = match[2]
        # Ignore spans with more than 8 words in them, and spans at the start of the doc
        if end - start > 8 or start == 1: continue
        if end - start > 3:
            # Long form is inside the parens.
            # Take one word before.
            short_form_candidate = doc[start - 2 : start - 1]
            long_form_candidate  = doc[start:end]
        else:
            # Normal case.
            # Short form is inside the parens.
            short_form_candidate = doc[start:end]
            # Sum character lengths of contents of parens.
            abbreviation_length = sum([len(x) for x in short_form_candidate])
            max_words = min(abbreviation_length + 5, abbreviation_length * 2)
            # Look up to max_words backwards
            long_form_candidate = doc[max(start - max_words - 1, 0) : start - 1]

        # add candidate to candidates if candidates pass filters
        if short_form_filter(short_form_candidate):
            candidates.append((long_form_candidate, short_form_candidate))

    return candidates


# FUNCTION 2
def short_form_filter(span: Span) -> bool:
    # All words are between length 2 and 10
    if not all([2 <= len(x) < 10 for x in span]):
        return False
    # At least 50% of the short form should be alpha
    if (sum([c.isalpha() for c in span.text]) / len(span.text)) < 0.5:
        return False
    # The first character of the short form should be alpha
    if not span.text[0].isalpha():
        return False
    return True


# FUNCTION 3
def find_abbreviation(long_form_candidate: Span, short_form_candidate: Span) -> Tuple[Span, Optional[Span]]:
    # The algorithm works by enumerating the characters in the short form of the abbreviation,
    # checking that they can be matched against characters in a candidate text for the long form
    # in order, as well as requiring that the first letter of the abbreviated form matches the
    # _beginning_ letter of a word.
    long_form   = " ".join([x.text for x in long_form_candidate])
    short_form  = " ".join([x.text for x in short_form_candidate])
    long_index  = len(long_form)  - 1
    short_index = len(short_form) - 1

    while short_index >= 0:
        current_char = short_form[short_index].lower()
        # No check over no alphanumeric character
        if not current_char.isalnum():
            short_index -= 1
            continue

        # condition 1: the current character is different to the long term one
        cond1 = long_index >= 0 and long_form[long_index].lower() != current_char
        # condition 2: checking the first character of the abbreviation, so _starting_ character of a span
        cond2 = short_index == 0 and long_index > 0 and long_form[long_index - 1].isalnum()
        while cond1 or cond2:
            long_index -= 1
            cond1 = long_index  >= 0 and long_form[long_index].lower() != current_char
            cond2 = short_index == 0 and long_index > 0 and long_form[long_index - 1].isalnum()

        # NO MATCHING
        if long_index < 0: return short_form_candidate, None

        long_index  -= 1
        short_index -= 1

    # An one value is added to get back to the start character of the long form
    long_index += 1

    # character index to span translation
    word_lengths = 0
    starting_index = None
    for i, word in enumerate(long_form_candidate):
        # need to add 1 for the space characters
        word_lengths += len(word.text_with_ws)
        if word_lengths > long_index:
            starting_index = i
            break
    return short_form_candidate, long_form_candidate[starting_index:]


# FUNCTION 3
def find_matches_for(filtered: List[Tuple[Span, Span]], doc: Doc, global_matcher) -> Dict:  # List[Tuple[Span, Set[Span]]]:
    rules = {}
    all_occurences: Dict[Span, Set[Span]] = defaultdict(set)
    already_seen_long:  Set[str] = set()
    already_seen_short: Set[str] = set()
    for (long_candidate, short_candidate) in filtered:
        short, long = find_abbreviation(long_candidate, short_candidate)
        # long and short form definitions have to be unique, because stored in a key-value structure.
        # If an abbreviation is defined twice in a document, there is not much to do.
        # The case which is discarded will be picked up below by the global matcher.
        new_long  = long.text  not in already_seen_long if long else False
        new_short = short.text not in already_seen_short
        if long is not None and new_long and new_short:
            already_seen_long.add(long.text)
            already_seen_short.add(short.text)
            all_occurences[long].add(short)
            rules[long.text] = long
            # Add a rule to a matcher to find exactly this substring.
            global_matcher.add(long.text, [[{"ORTH": x.text} for x in short]])

    # NEW
    long_short_occurrences = dict()
    for long_terms, short_terms in all_occurences.items():
        for short_term in short_terms:
            occurrences = [[short_term.text, m.start(), m.end()] for m in re.finditer(short_term.text, doc.text)][1:]
            long_short_occurrences[long_terms] = occurrences
    return long_short_occurrences


def abbreviation_handler(nlp: Language, doc: Doc):
    matcher = Matcher(nlp.vocab)
    matcher.add("parenthesis", [[{"ORTH": "("}, {"OP": "+"}, {"ORTH": ")"}]])
    global_matcher = Matcher(nlp.vocab)

    matches = matcher(doc)
    matches_no_brackets = [(x[0], x[1] + 1, x[2] - 1) for x in matches]
    filtered   = filter_matches(matches_no_brackets, doc)
    occurences = find_matches_for(filtered, doc, global_matcher)

    return occurences
